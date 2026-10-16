<?php

namespace App\Console\Commands;

use App\Support\LocalMediaStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SyncSupplierProducts extends Command
{
    protected $signature = 'supplier:sync-products
        {supplier=all}
        {--limit=0}
        {--dry-run}
        {--include-unmapped}
        {--product-data : Use the supplier product-data feed instead of the hourly price list}
        {--sync-images : Download product images from product-data feed}
        {--remote-images : Store image URLs without downloading them}
        {--job-id= : Supplier feed run id for progress reporting}';

    protected $description = 'Import or update products from supplier feeds using bridge category mappings.';

    private array $brandCache = [];

    private array $mappingCache = [];

    private array $columns = [];

    private ?int $jobId = null;

    public function __construct(private readonly LocalMediaStore $mediaStore)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', '0');

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        DB::disableQueryLog();
        $this->jobId = (int) $this->option('job-id') ?: null;

        try {
            foreach (['suppliers', 'products', 'product_prices', 'supplier_products', 'brands', 'supplier_category_mappings'] as $table) {
                if (! Schema::hasTable($table)) {
                    throw new \RuntimeException('Missing table: ' . $table);
                }

                $this->columns[$table] = Schema::getColumnListing($table);
            }

            $this->ensureConfiguredSuppliers();

            $supplierArgument = (string) $this->argument('supplier');
            $query = DB::table('suppliers');

            if ($supplierArgument === 'all') {
                $query->where('active', true);
            }
            if ($supplierArgument !== 'all') {
                $query->where(function ($builder) use ($supplierArgument): void {
                    $builder->where('slug', $supplierArgument)->orWhere('id', (int) $supplierArgument);
                });
            }

            $suppliers = $query->orderBy('name')->get();

            if ($suppliers->isEmpty()) {
                $this->warn('No active suppliers found.');

                return self::SUCCESS;
            }

            foreach ($suppliers as $supplier) {
                $this->syncSupplier($supplier);
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->updateRun('failed', 'Σφάλμα', 'Ο συγχρονισμός απέτυχε.', [
                'error' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function syncSupplier(object $supplier): void
    {
        $syncProductData = (bool) $this->option('product-data');
        $url = $syncProductData ? $this->productDataUrl($supplier) : trim((string) ($supplier->api_url ?? ''));

        if ($url === '') {
            $this->warn($supplier->name . ': missing API URL.');
            $this->updateRun('failed', 'Ρύθμιση', $supplier->name . ': λείπει API URL.', [
                'supplier_id' => (int) $supplier->id,
                'completed_at' => now(),
            ]);

            return;
        }

        $feedType = strtolower((string) ($supplier->feed_type ?? 'csv'));

        if ($feedType === 'json') {
            $this->syncJsonSupplier($supplier, $url, $syncProductData);

            return;
        }

        if ($feedType === 'xml') {
            $this->syncGenericXmlSupplier($supplier, $url);

            return;
        }

        if ($feedType !== 'csv') {
            $this->warn($supplier->name . ': product sync currently supports CSV feeds.');
            $this->updateRun('failed', 'Τύπος feed', $supplier->name . ': το product sync υποστηρίζει CSV feeds.', [
                'supplier_id' => (int) $supplier->id,
                'completed_at' => now(),
            ]);

            return;
        }

        $this->info('Syncing supplier: ' . $supplier->name . ($syncProductData ? ' product data' : ' price list'));
        $this->updateRun('running', 'Σύνδεση', 'Ξεκίνησε ο συγχρονισμός για ' . $supplier->name . '.', [
            'supplier_id' => (int) $supplier->id,
            'started_at' => now(),
        ]);

        $client = Http::timeout($syncProductData ? 600 : 300)->connectTimeout(25)->retry(1, 1500);

        if (($supplier->auth_type ?? null) === 'basic' && trim((string) ($supplier->username ?? '')) !== '') {
            $client = $client->withBasicAuth((string) $supplier->username, (string) ($supplier->password ?? ''));
        }

        $this->updateRun('running', 'Download feed', $syncProductData ? 'Κατεβαίνει product-data feed και εικόνες metadata.' : 'Κατεβαίνει price/stock feed.');
        $lastProgressAt = 0;
        $client = $client->withOptions([
            'progress' => function ($downloadTotal, $downloaded) use (&$lastProgressAt): void {
                if ($downloaded <= 0 || time() === $lastProgressAt) {
                    return;
                }

                $lastProgressAt = time();
                $this->updateRun('running', 'Download feed', $this->downloadMessage((float) $downloaded, (float) $downloadTotal));
            },
        ]);

        try {
            $response = $client->get($url);
            $response->throw();
            $body = $response->body();
        } catch (\Throwable $downloadException) {
            $this->updateRun('running', 'Download fallback', 'Το Laravel HTTP download απέτυχε. Δοκιμή με curl fallback.');
            $body = $this->downloadWithCurl($url, $supplier);
        }

        $this->updateRun('running', 'Ανάλυση CSV', 'Το feed κατέβηκε. Γίνεται ανάγνωση γραμμών.');

        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');
        $includeUnmapped = (bool) $this->option('include-unmapped');

        $stats = [
            'rows' => 0,
            'skipped_unmapped' => 0,
            'created' => 0,
            'updated' => 0,
            'supplier_rows' => 0,
            'image_candidates' => 0,
            'images' => 0,
        ];

        foreach ($this->csvRows($body) as $row) {
            if ($limit > 0 && $stats['rows'] >= $limit) {
                break;
            }

            $stats['rows']++;

            $categoryPath = $this->csvCategoryPath($row);

            $categoryId = $this->mappedCategoryId((int) $supplier->id, $categoryPath);
            $stats['image_candidates'] += $syncProductData ? count($this->imageUrls($row)) : 0;

            if (! $categoryId) {
                $stats['skipped_unmapped']++;
            }

            if ($dryRun) {
                continue;
            }

            $result = $this->upsertProduct((int) $supplier->id, $row, $categoryPath, $categoryId, $syncProductData);

            $stats[$result['created'] ? 'created' : 'updated']++;
            $stats['supplier_rows']++;
            $stats['images'] += $result['images'] ?? 0;

            if ($stats['rows'] % 1000 === 0) {
                $this->line('Rows: ' . $stats['rows'] . ' | created: ' . $stats['created'] . ' | updated: ' . $stats['updated'] . ' | skipped unmapped: ' . $stats['skipped_unmapped']);
                $this->updateRun('running', 'Επεξεργασία προϊόντων', 'Έχουν διαβαστεί ' . $stats['rows'] . ' γραμμές.', [
                    'processed_rows' => $stats['rows'],
                    'products_created' => $stats['created'],
                    'products_updated' => $stats['updated'],
                    'products_skipped' => $stats['skipped_unmapped'],
                    'image_candidates' => $stats['image_candidates'],
                    'images_saved' => $stats['images'],
                ]);
            }
        }

        if (! $dryRun) {
            DB::table('suppliers')->where('id', $supplier->id)->update([
                $syncProductData ? 'last_bridge_sync_at' : 'last_sync_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($stats as $key => $value) {
            $this->info($supplier->name . ' ' . str_replace('_', ' ', $key) . ': ' . $value);
        }

        $this->updateRun('completed', 'Ολοκληρώθηκε', 'Ο συγχρονισμός ολοκληρώθηκε για ' . $supplier->name . '.', [
            'processed_rows' => $stats['rows'],
            'products_created' => $stats['created'],
            'products_updated' => $stats['updated'],
            'products_skipped' => $stats['skipped_unmapped'],
            'image_candidates' => $stats['image_candidates'],
            'images_saved' => $stats['images'],
            'completed_at' => now(),
        ]);
    }

    private function ensureConfiguredSuppliers(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        $now = now();

        foreach ((array) config('services.suppliers', []) as $slug => $supplier) {
            $priceUrl = trim((string) ($supplier['price_url'] ?? ''));

            if ($priceUrl === '') {
                continue;
            }

            DB::table('suppliers')->updateOrInsert(
                ['slug' => (string) $slug],
                [
                    'name' => (string) ($supplier['name'] ?? Str::headline((string) $slug)),
                    'storefront_code' => trim((string) ($supplier['storefront_code'] ?? '')) ?: null,
                    'api_url' => $priceUrl,
                    'product_data_url' => trim((string) ($supplier['product_data_url'] ?? '')) ?: null,
                    'feed_type' => (string) ($supplier['feed_type'] ?? 'xml'),
                    'auth_type' => $supplier['auth_type'] ?? null,
                    'username' => $supplier['username'] ?? null,
                    'password' => $supplier['password'] ?? null,
                    'api_token' => $supplier['api_token'] ?? null,
                    'active' => (bool) ($supplier['active'] ?? true),
                    'updated_at' => $now,
                    'created_at' => $now,
                ],
            );
        }
    }

    private function syncGenericXmlSupplier(object $supplier, string $url): void
    {
        $maxRows = max((int) $this->option('limit'), 0);
        $dryRun = (bool) $this->option('dry-run');
        $includeUnmapped = (bool) $this->option('include-unmapped');
        $stats = [
            'rows' => 0,
            'skipped_unmapped' => 0,
            'created' => 0,
            'updated' => 0,
            'supplier_rows' => 0,
            'image_candidates' => 0,
            'images' => 0,
        ];

        $this->updateRun('running', 'Σύνδεση', 'Ξεκίνησε XML συγχρονισμός για ' . $supplier->name . '.', [
            'supplier_id' => (int) $supplier->id,
            'started_at' => now(),
        ]);

        $client = Http::timeout(900)->connectTimeout(25)->retry(1, 1500);

        if (($supplier->auth_type ?? null) === 'basic' && trim((string) ($supplier->username ?? '')) !== '') {
            $client = $client->withBasicAuth((string) $supplier->username, (string) ($supplier->password ?? ''));
        }

        $this->updateRun('running', 'Download XML', 'Κατεβαίνει XML feed για ' . $supplier->name . '.');

        try {
            $response = $client->get($url);
            $response->throw();
            $body = $response->body();
        } catch (\Throwable $downloadException) {
            $this->updateRun('running', 'Download fallback', 'Το Laravel HTTP download απέτυχε. Δοκιμή με curl fallback.');
            $body = $this->downloadWithCurl($url, $supplier);
        }

        $this->updateRun('running', 'Ανάλυση XML', 'Το XML κατέβηκε (' . number_format(strlen($body) / 1024, 1) . ' KB). Γίνεται ανάγνωση προϊόντων.');

        foreach ($this->genericXmlRows($body) as $row) {
            if ($maxRows > 0 && $stats['rows'] >= $maxRows) {
                break;
            }

            $stats['rows']++;
            $categoryPath = $this->cleanText($row['kat1'] ?? '');
            $categoryId = $categoryPath !== '' ? $this->mappedCategoryId((int) $supplier->id, $categoryPath) : null;
            $stats['image_candidates'] += count($this->imageUrls($row));

            if (! $categoryId && ! $includeUnmapped) {
                $stats['skipped_unmapped']++;
                continue;
            }

            if ($dryRun) {
                continue;
            }

            $result = $this->upsertProduct((int) $supplier->id, $row, $categoryPath, $categoryId, true);

            $stats[$result['created'] ? 'created' : 'updated']++;
            $stats['supplier_rows']++;
            $stats['images'] += $result['images'] ?? 0;

            if ($stats['rows'] % 1000 === 0) {
                $this->updateRun('running', 'Επεξεργασία XML', 'Έχουν διαβαστεί ' . $stats['rows'] . ' προϊόντα.', [
                    'processed_rows' => $stats['rows'],
                    'products_created' => $stats['created'],
                    'products_updated' => $stats['updated'],
                    'products_skipped' => $stats['skipped_unmapped'],
                    'image_candidates' => $stats['image_candidates'],
                    'images_saved' => $stats['images'],
                ]);
            }
        }

        if (! $dryRun) {
            DB::table('suppliers')->where('id', $supplier->id)->update([
                'last_sync_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->updateRun('completed', 'Ολοκληρώθηκε', 'Ο XML συγχρονισμός ολοκληρώθηκε για ' . $supplier->name . '.', [
            'processed_rows' => $stats['rows'],
            'products_created' => $stats['created'],
            'products_updated' => $stats['updated'],
            'products_skipped' => $stats['skipped_unmapped'],
            'image_candidates' => $stats['image_candidates'],
            'images_saved' => $stats['images'],
            'completed_at' => now(),
        ]);
    }

    private function syncJsonSupplier(object $supplier, string $url, bool $syncProductData): void
    {
        $maxRows = max((int) $this->option('limit'), 0);
        $limit = $maxRows > 0 ? min($maxRows, 100) : 100;
        $dryRun = (bool) $this->option('dry-run');
        $includeUnmapped = (bool) $this->option('include-unmapped');
        $stats = [
            'rows' => 0,
            'skipped_unmapped' => 0,
            'created' => 0,
            'updated' => 0,
            'supplier_rows' => 0,
            'image_candidates' => 0,
            'images' => 0,
        ];

        $this->updateRun('running', 'Σύνδεση', 'Ξεκίνησε JSON συγχρονισμός για ' . $supplier->name . '.', [
            'supplier_id' => (int) $supplier->id,
            'started_at' => now(),
        ]);

        $client = $this->jsonClient($supplier);
        $page = 1;
        $lastPage = 1;

        do {
            $this->updateRun('running', 'Download page', 'Κατεβαίνει JSON page ' . $page . ' από ' . $lastPage . '.');

            $response = $client->get($url, $this->queryCredentials($supplier, [
                'limit' => $limit,
                'page' => $page,
            ]));
            $response->throw();

            $payload = $response->json();

            if (! is_array($payload)) {
                throw new \RuntimeException('The products response is not valid JSON.');
            }

            if (($payload['success'] ?? true) === false) {
                throw new \RuntimeException((string) data_get($payload, 'error.message', 'Supplier returned an error.'));
            }

            $products = data_get($payload, 'data.products', data_get($payload, 'products', []));
            $pagination = data_get($payload, 'data.pagination', data_get($payload, 'pagination', []));
            $lastPage = max(1, (int) ($pagination['last'] ?? $lastPage));

            foreach (is_array($products) ? $products : [] as $product) {
                if ($maxRows > 0 && $stats['rows'] >= $maxRows) {
                    break;
                }

                if (! is_array($product)) {
                    continue;
                }

                $stats['rows']++;
                $row = $this->jsonRow($product);
                $categoryRaw = $this->jsonCategoryPath($product) ?: $this->jsonCategoryCode($product);
                $categoryId = $categoryRaw !== '' ? $this->mappedCategoryId((int) $supplier->id, $categoryRaw) : null;
                $stats['image_candidates'] += count($this->imageUrls($row));

                if (! $categoryId && ! $includeUnmapped) {
                    $stats['skipped_unmapped']++;
                    continue;
                }

                if ($dryRun) {
                    continue;
                }

                $result = $this->upsertProduct((int) $supplier->id, $row, $categoryRaw, $categoryId, true);

                $stats[$result['created'] ? 'created' : 'updated']++;
                $stats['supplier_rows']++;
                $stats['images'] += $result['images'] ?? 0;
            }

            $this->updateRun('running', 'Επεξεργασία JSON', 'Έχουν διαβαστεί ' . $stats['rows'] . ' προϊόντα.', [
                'processed_rows' => $stats['rows'],
                'products_created' => $stats['created'],
                'products_updated' => $stats['updated'],
                'products_skipped' => $stats['skipped_unmapped'],
                'image_candidates' => $stats['image_candidates'],
                'images_saved' => $stats['images'],
            ]);

            $page++;
        } while ($page <= $lastPage && ($maxRows === 0 || $stats['rows'] < $maxRows));

        if (! $dryRun) {
            DB::table('suppliers')->where('id', $supplier->id)->update([
                'last_sync_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->updateRun('completed', 'Ολοκληρώθηκε', 'Ο JSON συγχρονισμός ολοκληρώθηκε για ' . $supplier->name . '.', [
            'processed_rows' => $stats['rows'],
            'products_created' => $stats['created'],
            'products_updated' => $stats['updated'],
            'products_skipped' => $stats['skipped_unmapped'],
            'image_candidates' => $stats['image_candidates'],
            'images_saved' => $stats['images'],
            'completed_at' => now(),
        ]);
    }

    private function jsonClient(object $supplier): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(300)->connectTimeout(25)->retry(1, 1500)->acceptJson();

        if (($supplier->auth_type ?? null) === 'basic' && trim((string) ($supplier->username ?? '')) !== '') {
            return $client->withBasicAuth((string) $supplier->username, (string) ($supplier->password ?? ''));
        }

        return $client;
    }

    private function queryCredentials(object $supplier, array $extra = []): array
    {
        if (($supplier->auth_type ?? null) !== 'query') {
            return $extra;
        }

        return array_merge([
            'user' => (string) ($supplier->username ?? ''),
            'pass' => (string) ($supplier->password ?? ''),
        ], $extra);
    }

    private function updateRun(string $status, string $stage, string $message, array $extra = []): void
    {
        if (! $this->jobId || ! Schema::hasTable('supplier_feed_runs')) {
            return;
        }

        $run = DB::table('supplier_feed_runs')->where('id', $this->jobId)->first();
        $log = $run && $run->log ? (json_decode((string) $run->log, true) ?: []) : [];
        $log[] = [
            'time' => now()->format('H:i:s'),
            'stage' => $stage,
            'message' => $message,
        ];

        DB::table('supplier_feed_runs')->where('id', $this->jobId)->update(array_merge([
            'status' => $status,
            'stage' => $stage,
            'message' => $message,
            'log' => json_encode(array_slice($log, -20), JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ], $extra));
    }

    private function downloadMessage(float $downloaded, float $total): string
    {
        $downloadedMb = number_format($downloaded / 1024 / 1024, 2);

        if ($total > 0) {
            return 'Κατεβαίνει το feed: ' . $downloadedMb . ' MB από ' . number_format($total / 1024 / 1024, 2) . ' MB.';
        }

        return 'Κατεβαίνει το feed: ' . $downloadedMb . ' MB μέχρι τώρα.';
    }

    private function upsertProduct(int $supplierId, array $row, string $categoryPath, ?int $categoryId, bool $syncProductData = false): array
    {
        $code = $this->cleanIdentifier($row['artnr'] ?? '');
        $mpn = $this->cleanIdentifier($row['herstnr'] ?? '');
        $ean = $this->cleanIdentifier($row['ean'] ?? '');
        $title = $this->cleanText(($syncProductData && $this->cleanText($row['title'] ?? '') !== '') ? $row['title'] : ($row['artname'] ?? ''));
        $brandName = $this->cleanText($row['hersteller'] ?? '');

        if ($title === '' && $code === '' && $mpn === '' && $ean === '') {
            return ['id' => null, 'created' => false];
        }

        $product = $this->findExistingProduct($code, $ean, $mpn);
        $productId = $product?->id;
        $created = ! $productId;
        $slug = $this->uniqueProductSlug($title !== '' ? $title : ($mpn ?: $code), $productId ? (int) $productId : null);
        $brandId = $this->findOrCreateBrand($brandName);
        $costExVat = $this->decimal($row['hek'] ?? null);
        $retailIncVat = $this->decimal($row['vkbrutto'] ?? null);
        $saleExVat = $costExVat !== null ? round($costExVat * 1.24, 4) : null;
        $saleIncVat = $saleExVat !== null ? round($saleExVat * 1.19, 4) : $retailIncVat;
        $quantity = $this->stockQuantity($row);
        $availability = $this->cleanText($row['verfuegbar'] ?? '');
        $now = now();

        $productData = [
            'code' => $code ?: null,
            'mpn' => $mpn ?: null,
            'ean' => $ean ?: null,
            'title' => $title ?: ($mpn ?: $code),
            'slug' => $slug,
            'brand_id' => $brandId,
            'category_id' => $categoryId,
            'primary_supplier_id' => $supplierId,
            'status' => (! $categoryId || $this->isEndOfLife($row)) ? 'inactive' : 'active',
            'vat_rate' => 19,
            'weight_kg' => $this->decimal($row['gewicht'] ?? null),
            'last_imported_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ];

        if ($syncProductData) {
            $productDataColumns = [
                'short_description' => $this->firstLongText($row, ['short_desc', 'short_summary']),
                'description' => $this->firstLongText($row, ['marketing_text', 'long_summary', 'specs']),
                'meta_description' => Str::limit(strip_tags($this->firstLongText($row, ['short_summary', 'short_desc'])), 250, ''),
            ];

            foreach ($productDataColumns as $column => $value) {
                if ($value !== '' && in_array($column, $this->columns['products'], true)) {
                    $productDataColumns[$column] = $value;
                    $productData[$column] = $value;
                }
            }

            if (in_array('specs', $this->columns['products'], true)) {
                $specs = $this->firstLongText($row, ['specs']);

                if ($specs !== '') {
                    $productData['specs'] = json_encode([
                        'html' => $specs,
                        'datasheet' => $this->cleanText($row['pdf'] ?? ''),
                        'manual' => $this->cleanText($row['pdf_manual'] ?? $row['manual_pdf'] ?? ''),
                    ], JSON_UNESCAPED_UNICODE);
                }
            }
        }

        foreach ([
            'quantity' => $quantity,
            'stock_text' => $this->availabilityText($availability),
            'available_from' => $this->dateOrNull($row['eta'] ?? null),
            'price_update_enabled' => true,
            'status_update_enabled' => true,
            'purchase_price_update_enabled' => true,
        ] as $column => $value) {
            if (in_array($column, $this->columns['products'], true)) {
                $productData[$column] = $value;
            }
        }

        if ($productId) {
            DB::table('products')->where('id', $productId)->update($productData);
        } else {
            $productData['created_at'] = $now;
            $productId = DB::table('products')->insertGetId($productData);
        }

        DB::table('product_prices')->updateOrInsert(
            ['product_id' => $productId],
            [
                'cost_ex_vat' => $costExVat,
                'retail_inc_vat' => $saleIncVat,
                'wholesale_inc_vat' => $saleExVat,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $images = $syncProductData ? $this->imageUrls($row) : [];
        $savedImages = $syncProductData ? $this->syncProductImages((int) $productId, $images, 'supplier:' . $supplierId) : 0;
        $primaryImage = $this->primaryImage((int) $productId);

        if ($primaryImage !== '') {
            $imageData = ['updated_at' => $now];

            foreach (['image_path', 'main_image'] as $column) {
                if (in_array($column, $this->columns['products'], true)) {
                    $imageData[$column] = $primaryImage;
                }
            }

            if (count($imageData) > 1) {
                DB::table('products')->where('id', $productId)->update($imageData);
            }
        }

        DB::table('supplier_products')->updateOrInsert(
            ['supplier_id' => $supplierId, 'supplier_sku' => $code],
            [
                'matched_product_id' => $productId,
                'mpn' => $mpn ?: null,
                'ean' => $ean ?: null,
                'brand_name' => $brandName ?: null,
                'title' => $title ?: ($mpn ?: $code),
                'category_raw' => $categoryPath ?: null,
                'cost_ex_vat' => $costExVat,
                'retail_inc_vat' => $saleIncVat,
                'stock_quantity' => $quantity,
                'availability' => $availability ?: null,
                'images' => $images !== [] ? json_encode($images, JSON_UNESCAPED_UNICODE) : null,
                'raw_data' => json_encode($row, JSON_UNESCAPED_UNICODE),
                'match_status' => $categoryId ? 'matched' : 'unmapped',
                'last_seen_at' => $now,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        return ['id' => (int) $productId, 'created' => $created, 'images' => $savedImages];
    }

    private function csvRows(string $csv): \Generator
    {
        $lines = preg_split('/\R/u', trim($csv)) ?: [];

        if ($lines === []) {
            return;
        }

        $delimiter = $this->detectCsvDelimiter($lines[0]);
        $headers = array_map(
            fn (string $header): string => strtolower(trim($header, " \t\n\r\0\x0B\"'")),
            str_getcsv($lines[0], $delimiter),
        );

        foreach (array_slice($lines, 1) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $values = str_getcsv($line, $delimiter);
            $row = [];

            foreach ($headers as $index => $header) {
                if ($header !== '') {
                    $row[$header] = $values[$index] ?? null;
                }
            }

            yield $row;
        }
    }

    private function detectCsvDelimiter(string $headerLine): string
    {
        $bestDelimiter = ';';
        $bestCount = 0;

        foreach ([',', ';', "\t", '|'] as $delimiter) {
            $count = count(str_getcsv($headerLine, $delimiter));

            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    private function productDataUrl(object $supplier): string
    {
        $explicit = trim((string) ($supplier->product_data_url ?? ''));

        if ($explicit !== '') {
            return $explicit;
        }

        return trim((string) ($supplier->api_url ?? ''));
    }

    private function downloadWithCurl(string $url, ?object $supplier = null): string
    {
        $command = 'curl -L --silent --show-error --max-time 900';

        if (
            $supplier
            && ($supplier->auth_type ?? null) === 'basic'
            && trim((string) ($supplier->username ?? '')) !== ''
        ) {
            $command .= ' --user ' . escapeshellarg((string) $supplier->username . ':' . (string) ($supplier->password ?? ''));
        }

        $command .= ' ' . escapeshellarg($url);
        $body = shell_exec($command);

        if (! is_string($body) || trim($body) === '') {
            throw new \RuntimeException('Could not download supplier feed with curl.');
        }

        return $body;
    }

    private function csvCategoryPath(array $row): string
    {
        $parts = [];

        foreach (['kat1', 'kat2', 'kat3', 'kat4', 'kat5', 'kat6'] as $key) {
            $value = $this->cleanText($row[$key] ?? '');

            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return implode(' / ', array_values(array_unique($parts)));
    }

    private function genericXmlRows(string $xml): \Generator
    {
        libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (! $document) {
            throw new \RuntimeException('The XML response is not valid XML.');
        }

        $nodes = $document->xpath('//*[local-name() = "product" or local-name() = "item" or local-name() = "article" or local-name() = "record"]') ?: [];

        foreach ($nodes as $node) {
            $code = $this->firstXmlChildText($node, ['artnr', 'sku', 'supplier_sku', 'product_id', 'productid', 'id', 'code', 'item_code', 'itemcode']);
            $title = $this->firstXmlChildText($node, ['artname', 'title', 'name', 'product_name', 'products_name', 'description1', 'description']);
            $mpn = $this->firstXmlChildText($node, ['herstnr', 'mpn', 'msku', 'manufacturer_part_number', 'manufacturerpartnumber', 'manufacturer_code', 'manufacturer_sku', 'mfr_part_number', 'part_number']);
            $ean = $this->firstXmlChildText($node, ['ean', 'ean13', 'ean-13', 'barcode', 'gtin']);

            if ($code === '' && $title === '' && $mpn === '' && $ean === '') {
                continue;
            }

            $categoryPath = $this->genericXmlCategoryPath($node);

            yield [
                'artnr' => $code,
                'herstnr' => $mpn,
                'ean' => $ean,
                'artname' => $title,
                'title' => $title,
                'hersteller' => $this->firstXmlChildText($node, ['hersteller', 'manufacturer', 'manufacturer_name', 'manufacturers_name', 'brand', 'brand_name']),
                'hek' => $this->decimal($this->firstXmlChildText($node, ['hek', 'purchase_price', 'net_price', 'price_net', 'products_price', 'price', 'cost'])),
                'vkbrutto' => $this->decimal($this->firstXmlChildText($node, ['vkbrutto', 'retail_price', 'suggested_retail_price', 'rrp', 'msrp', 'price_gross'])),
                'menge' => $this->firstXmlChildText($node, ['menge', 'stock', 'quantity', 'qty', 'available_quantity', 'availability_quantity']),
                'verfuegbar' => $this->firstXmlChildText($node, ['verfuegbar', 'availability', 'stock_status', 'status']),
                'kat1' => $categoryPath,
                'short_desc' => $this->firstXmlChildText($node, ['short_desc', 'short_description', 'description1', 'summary']),
                'marketing_text' => $this->firstXmlChildText($node, ['marketing_text', 'long_description', 'products_description', 'description', 'description2']),
                'gewicht' => $this->decimal($this->firstXmlChildText($node, ['gewicht', 'weight', 'products_weight'])),
                'images_xl' => implode(';', $this->genericXmlImageUrls($node)),
            ];
        }
    }

    private function genericXmlCategoryPath(\SimpleXMLElement $node): string
    {
        $fullPath = $this->firstXmlChildText($node, ['fullpath', 'full_path', 'category_path', 'categorypath', 'breadcrumb']);

        if ($fullPath !== '') {
            $parts = collect(preg_split('/\s*(?:>|\/)\s*/u', $fullPath) ?: [])
                ->map(fn (string $part): string => $this->cleanText($part))
                ->filter(fn (string $part): bool => $part !== '' && ! in_array(Str::lower($part), ['home'], true))
                ->values()
                ->all();

            return $parts === [] ? $fullPath : implode(' / ', $parts);
        }

        $parts = [];

        foreach ([
            'categories',
            'category',
            'category_name',
            'category_path',
            'category1',
            'category2',
            'category3',
            'category4',
            'kat1',
            'kat2',
            'kat3',
            'kat4',
            'kat5',
            'kat6',
            'lwg1',
            'lwg2',
            'lwg3',
            'lwg4',
            'lwg5',
            'lwg6',
            'warengruppe',
            'warenbereich',
            'produktgruppe',
            'productgroup',
            'product_group',
            'group',
            'hauptgruppe',
            'untergruppe',
            'section',
        ] as $field) {

            $value = $this->firstXmlChildText($node, [$field]);

            $value = str_replace('|', '/', $value);

            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return implode(' / ', array_values(array_unique($parts)));
    }

    private function genericXmlImageUrls(\SimpleXMLElement $node): array
    {
        $urls = [];

        foreach ([
            'image', 'images', 'image_url', 'imageurl', 'image_link', 'image_links',
            'imageLinks', 'imagelinks', 'image_large', 'large_image', 'main_image',
            'products_image', 'product_image', 'picture', 'pictures', 'picture_url',
            'pictureurl', 'photo', 'photos', 'thumbnail', 'gallery', 'media', 'url',
            'image1', 'image2', 'image3', 'image4', 'image5',
        ] as $name) {
            foreach ($node->xpath('.//*[local-name() = "' . $name . '"]') ?: [] as $imageNode) {
                $value = trim((string) $imageNode);

                foreach ($this->splitImageCandidates($value) as $candidate) {
                    if ($this->isImageUrl($candidate)) {
                        $urls[] = $candidate;
                    }
                }
            }
        }

        return array_values(array_unique($urls));
    }

    private function firstXmlChildText(\SimpleXMLElement $node, array $names): string
    {
        foreach ($names as $name) {
            $lowerName = mb_strtolower($name);

            $result = $node->xpath(
                './/*[translate(local-name(), "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz") = "' . $lowerName . '"]'
            );

            if ($result && isset($result[0])) {
                $value = $this->cleanText((string) $result[0]);

                if ($value !== '') {
                    return $value;
                }
            }
        }

        return '';
    }

    private function jsonRow(array $product): array
    {
        $images = $this->jsonImages($product);

        return [
            'artnr' => $this->firstJsonText($product, ['sku', 'api_code', 'code', 'item_code', 'id']),
            'herstnr' => $this->firstJsonText($product, ['mpn', 'manufacturer_code', 'model', 'api_code']),
            'ean' => $this->firstJsonText($product, ['barcode', 'ean', 'gtin']),
            'artname' => $this->firstJsonText($product, ['name', 'title', 'description']),
            'title' => $this->firstJsonText($product, ['name', 'title', 'description']),
            'hersteller' => $this->firstJsonText($product, ['brand', 'manufacturer', 'brand_name']),
            'hek' => $this->firstJsonText($product, ['price', 'net_price', 'wholesale_price']),
            'vkbrutto' => $this->firstJsonText($product, ['retail_price', 'price_with_vat', 'price']),
            'menge' => $this->firstJsonText($product, ['stock', 'quantity', 'qty', 'availability_qty']),
            'verfuegbar' => $this->firstJsonText($product, ['availability', 'stock_status', 'status']),
            'short_desc' => $this->firstJsonText($product, ['short_description', 'summary']),
            'marketing_text' => $this->firstJsonText($product, ['long_description', 'description_html', 'description']),
            'images_xl' => implode(';', $images),
        ];
    }

    private function jsonCategoryCode(array $product): string
    {
        $path = $this->jsonCategoryPath($product);

        if ($path !== '') {
            return $path;
        }

        $category = $product['category'] ?? $product['main_category'] ?? null;

        if (is_array($category)) {
            return $this->cleanIdentifier($category['id'] ?? $category['category_id'] ?? $category['code'] ?? '');
        }

        foreach (['category_id', 'category', 'category_code'] as $field) {
            $value = $product[$field] ?? null;

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        $categories = $product['categories'] ?? [];

        if (is_array($categories)) {
            foreach ($categories as $categoryItem) {
                if (is_array($categoryItem)) {
                    $id = $categoryItem['id'] ?? $categoryItem['category_id'] ?? $categoryItem['code'] ?? null;

                    if ($id !== null && trim((string) $id) !== '') {
                        return trim((string) $id);
                    }
                } elseif (is_scalar($categoryItem) && trim((string) $categoryItem) !== '') {
                    return trim((string) $categoryItem);
                }
            }
        }

        return '';
    }

    private function jsonCategoryPath(array $product): string
    {
        $parts = [];

        foreach (['category_name', 'category_path'] as $field) {
            $value = $this->cleanText($product[$field] ?? '');

            if ($value !== '') {
                return $value;
            }
        }

        $category = $product['category'] ?? $product['main_category'] ?? null;

        if (is_array($category)) {
            $name = $this->cleanText($category['name'] ?? $category['title'] ?? '');

            if ($name !== '') {
                $parts[] = $name;
            }
        }

        $categories = $product['categories'] ?? [];

        if (is_array($categories)) {
            foreach ($categories as $categoryItem) {
                if (is_array($categoryItem)) {
                    $name = $this->cleanText($categoryItem['name'] ?? $categoryItem['title'] ?? '');

                    if ($name !== '') {
                        $parts[] = $name;
                    }
                } elseif (is_scalar($categoryItem)) {
                    $name = $this->cleanText($categoryItem);

                    if ($name !== '') {
                        $parts[] = $name;
                    }
                }
            }
        }

        return implode(' / ', array_values(array_unique($parts)));
    }

    private function firstJsonText(array $product, array $keys): string
    {
        foreach ($keys as $key) {
            $value = data_get($product, $key);

            if (is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }

    private function jsonImages(array $product): array
    {
        $values = [];

        foreach ([
            'image', 'image_url', 'imageUrl', 'image_link', 'imageLinks', 'images_xl',
            'main_image', 'mainImage', 'large_image', 'thumbnail', 'thumb', 'photo',
            'photos', 'picture', 'pictures', 'images', 'gallery', 'media', 'assets',
        ] as $field) {
            $value = data_get($product, $field);

            if (is_string($value)) {
                $values[] = $value;
            } elseif (is_array($value)) {
                foreach ($value as $item) {
                    if (is_string($item)) {
                        $values[] = $item;
                    } elseif (is_array($item)) {
                        foreach (['url', 'image', 'image_url', 'imageUrl', 'src', 'path', 'href', 'large', 'medium', 'thumbnail'] as $urlField) {
                            if (isset($item[$urlField]) && is_string($item[$urlField])) {
                                $values[] = $item[$urlField];
                                break;
                            }
                        }
                    }
                }
            }
        }

        return collect($values)
            ->flatMap(fn ($url): array => $this->splitImageCandidates((string) $url))
            ->filter(fn (string $url): bool => $this->isImageUrl($url))
            ->unique()
            ->values()
            ->all();
    }

 private function mappedCategoryId(int $supplierId, string $categoryPath): ?int
    {
        if (! isset($this->mappingCache[$supplierId])) {
            $rows = DB::table('supplier_category_mappings')
                ->where('supplier_id', $supplierId)
                ->get(['category_id', 'remote_code', 'remote_name']);

            $this->mappingCache[$supplierId] = [];

            foreach ($rows as $row) {
                foreach ([$row->remote_code, $row->remote_name] as $key) {
                    $key = $this->normalizeCategoryKey((string) $key);

                    if ($key !== '') {
                        $this->mappingCache[$supplierId][$key] = (int) $row->category_id;
                    }
                }
            }
        }

        $mappings = $this->mappingCache[$supplierId];
        $path = $this->normalizeCategoryKey($categoryPath);

        while ($path !== '') {
            if (isset($mappings[$path])) {
                return (int) $mappings[$path];
            }

            $parts = explode(' / ', $path);
            array_pop($parts);
            $path = implode(' / ', $parts);
        }

        return null;
    }

    private function normalizeCategoryKey(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s*\|\s*/', ' / ', $value);
        $value = preg_replace('/\s*>\s*/', ' / ', $value);
        $value = preg_replace('/\s*\/\s*/', ' / ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return mb_strtoupper(trim($value));
    }

    private function findExistingProduct(string $code, string $ean, string $mpn): ?object
    {
        if ($code === '' && $ean === '' && $mpn === '') {
            return null;
        }

        return DB::table('products')
            ->when(in_array('deleted_at', $this->columns['products'], true), fn ($query) => $query->whereNull('deleted_at'))
            ->where(function ($query) use ($code, $ean, $mpn): void {
                if ($code !== '') {
                    $query->orWhere('code', $code);
                }

                if ($ean !== '') {
                    $query->orWhere('ean', $ean);
                }

                if ($mpn !== '') {
                    $query->orWhere('mpn', $mpn);
                }
            })
            ->orderByRaw('case when code = ? then 0 else 1 end', [$code])
            ->first();
    }

    private function findOrCreateBrand(string $brandName): ?int
    {
        if ($brandName === '') {
            return null;
        }

        if (isset($this->brandCache[$brandName])) {
            return $this->brandCache[$brandName];
        }

        $existing = DB::table('brands')->where('name', $brandName)->value('id');

        if ($existing) {
            return $this->brandCache[$brandName] = (int) $existing;
        }

        return $this->brandCache[$brandName] = (int) DB::table('brands')->insertGetId([
            'name' => $brandName,
            'slug' => $this->uniqueBrandSlug($brandName),
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function uniqueBrandSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'brand';
        $slug = $base;
        $index = 2;

        while (DB::table('brands')->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $index++;
        }

        return $slug;
    }

    private function uniqueProductSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug(Str::limit($title, 90, '')) ?: 'product';
        $slug = $base;
        $index = 2;

        while (DB::table('products')
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $index++;
        }

        return $slug;
    }

    private function cleanText($value): string
    {
        return preg_replace('/\s+/u', ' ', trim((string) $value)) ?: '';
    }

    private function cleanLongText($value): string
    {
        $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(
            ['#013#010', '#010#013', '#013', '#010', '\\r\\n', '\\n', '\\r'],
            "\n",
            $text
        );
        $text = preg_replace("/\r\n|\r/u", "\n", $text) ?: $text;
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?: $text;
        $text = preg_replace('/[ \t]*\n[ \t]*/u', "\n", $text) ?: $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?: $text;

        return trim($text);
    }

    private function firstText(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($row[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function firstLongText(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            $value = $this->cleanLongText($row[$key] ?? '');

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function cleanIdentifier($value): string
    {
        return trim((string) $value);
    }

    private function decimal($value): ?float
    {
        $value = str_replace(',', '.', trim((string) $value));

        return $value === '' ? null : (float) $value;
    }

    private function stockQuantity(array $row): int
    {
        $stock = (int) $this->decimal($row['menge'] ?? 0);

        return max(0, min(100, $stock));
    }

    private function availabilityText(string $availability): string
    {
        return match (strtoupper($availability)) {
            'V' => 'Available',
            'B' => 'Ordered',
            'A' => 'External warehouse',
            default => 'Upon order',
        };
    }

    private function isEndOfLife(array $row): bool
    {
        return (int) $this->decimal($row['eol'] ?? 0) === 1 && $this->stockQuantity($row) <= 0;
    }

    private function dateOrNull($value): ?string
    {
        $value = trim((string) $value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;
    }

    private function imageUrls(array $row): array
    {
        foreach (['images_xl', 'image_xl', 'images_l', 'image_l', 'images_m', 'image_m', 'images_s', 'image_s'] as $field) {
            $value = trim((string) ($row[$field] ?? ''));

            if ($value === '') {
                continue;
            }

            return collect($this->splitImageCandidates($value))
                ->filter(fn (string $url): bool => $this->isImageUrl($url))
                ->unique()
                ->values()
                ->all();
        }

        return $this->csvImageUrls($row);
    }

    private function csvImageUrls(array $row): array
    {
        $values = [];
        $preferredFields = [
            'image', 'images', 'image_url', 'imageurl', 'image_link', 'imagelink',
            'image_links', 'imagelinks', 'imageLinks', 'image_xl', 'images_xl',
            'image_l', 'images_l', 'image_m', 'images_m', 'main_image',
            'large_image', 'product_image', 'products_image', 'picture', 'pictures',
            'picture_url', 'pictureurl', 'photo', 'photos', 'thumbnail', 'thumb',
            'gallery', 'media', 'image1', 'image2', 'image3', 'image4', 'image5',
            'bild', 'bild_url', 'bildurl',
        ];

        foreach ($preferredFields as $field) {
            foreach ($this->rowValuesForField($row, $field) as $value) {
                $values[] = $value;
            }
        }

        foreach ($row as $field => $value) {
            $field = strtolower((string) $field);

            if (! str_contains($field, 'image') && ! str_contains($field, 'picture') && ! str_contains($field, 'photo') && ! str_contains($field, 'bild')) {
                continue;
            }

            $values[] = $value;
        }

        return collect($values)
            ->flatMap(fn ($value): array => $this->splitImageCandidates((string) $value))
            ->filter(fn (string $url): bool => $this->isImageUrl($url))
            ->unique()
            ->values()
            ->all();
    }

    private function rowValuesForField(array $row, string $field): array
    {
        $values = [];
        $target = strtolower($field);

        foreach ($row as $key => $value) {
            if (strtolower((string) $key) === $target && trim((string) $value) !== '') {
                $values[] = $value;
            }
        }

        return $values;
    }

    private function splitImageCandidates(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [];
        }

        if (str_starts_with($value, '[') || str_starts_with($value, '{')) {
            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                return $this->flattenImageJson($decoded);
            }
        }

        preg_match_all('~https?://[^\s;,|"\']+~i', $value, $matches);
        $candidates = $matches[0] ?? [];

        if ($candidates === []) {
            $candidates = preg_split('/[;\r\n|]+/u', $value) ?: [];
        }

        return collect($candidates)
            ->map(fn ($url): string => trim((string) $url, " \t\n\r\0\x0B\"'"))
            ->filter()
            ->values()
            ->all();
    }

    private function flattenImageJson(array $value): array
    {
        $urls = [];

        foreach ($value as $item) {
            if (is_string($item)) {
                $urls[] = $item;
            } elseif (is_array($item)) {
                foreach (['url', 'image', 'image_url', 'imageUrl', 'src', 'path', 'href', 'large', 'medium', 'thumbnail'] as $urlKey) {
                    if (isset($item[$urlKey]) && is_string($item[$urlKey])) {
                        $urls[] = $item[$urlKey];
                    }
                }

                $urls = array_merge($urls, $this->flattenImageJson($item));
            }
        }

        return $urls;
    }

    private function isImageUrl(string $url): bool
    {
        $url = trim($url, " \t\n\r\0\x0B\"'");

        if (! Str::startsWith($url, ['http://', 'https://'])) {
            return false;
        }

        $path = strtolower((string) parse_url($url, PHP_URL_PATH));

        if (preg_match('/\.(?:jpg|jpeg|png|webp|gif|avif|bmp|svg)$/i', $path)) {
            return true;
        }

        return str_contains($path, 'image')
            || str_contains($path, 'img')
            || str_contains($path, 'photo')
            || str_contains($path, 'picture')
            || str_contains($path, 'thumbnail')
            || str_contains($path, 'media');
    }

    private function syncProductImages(int $productId, array $urls, string $source): int
    {
        if ($urls === [] || ! (bool) $this->option('sync-images') || ! Schema::hasTable('product_images')) {
            return 0;
        }

        DB::table('product_images')
            ->where('product_id', $productId)
            ->where('source', $source)
            ->delete();

        $rows = [];
        $now = now();
        $remoteOnly = (bool) $this->option('remote-images');

        foreach ($urls as $index => $url) {
            $path = $remoteOnly ? '' : $this->mediaStore->localize($url, 'product-images/' . $productId);

            $rows[] = [
                'product_id' => $productId,
                'url' => $url,
                'path' => $path !== '' && ! $this->mediaStore->isRemote($path) ? $path : null,
                'source' => $source,
                'sort_order' => ($index + 1) * 10,
                'is_primary' => $index === 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows !== []) {
            DB::table('product_images')->insert($rows);
        }

        return count($rows);
    }

    private function primaryImage(int $productId): string
    {
        if (! Schema::hasTable('product_images')) {
            return '';
        }

        $image = DB::table('product_images')
            ->where('product_id', $productId)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->first();

        return $image
            ? trim((string) ($image->path ?: $image->url ?: ''))
            : '';
    }
}
