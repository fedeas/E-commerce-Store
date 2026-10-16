<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use UnitEnum;

class SupplierBridges extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $slug = 'suppliers/xml';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.supplier-bridges';

    public ?int $selectedSupplierId = null;

    public string $supplierSearch = '';

    public string $supplierName = '';

    public string $apiUrl = '';

    public string $feedType = 'csv';

    public string $purchaseMarkupPercent = '0';

    public string $saleMarkupPercent = '24';

    public string $availableText = 'Available from 4 to 10 days';

    public string $unavailableText = 'Upon order';

    public bool $updateQuantities = true;

    public bool $updatePurchasePrice = true;

    public bool $updateSalePrice = false;

    public string $purchasePriceVatMode = 'ex_vat';

    public string $salePriceVatMode = 'ex_vat';

    public bool $active = true;

    public string $remoteCategoryName = '';

    public string $remoteCategoryCode = '';

    public string $remoteCategorySearch = '';

    public string $localCategorySearch = '';

    public array $localCategoryIds = [];

    public array $localCategorySearches = [];

    public string $productDataUrl = '';

    public ?int $activeFeedRunId = null;

    public function mount(): void
    {
        $this->ensureConfiguredSuppliers();

        $this->selectedSupplierId = DB::table('suppliers')->orderBy('name')->value('id');

        $this->loadSelectedSupplier();
    }

    public function getViewData(): array
    {
        return [
            'suppliers' => $this->suppliersForView(),
            'selectedSupplier' => $this->selectedSupplier(),
            'remoteCategories' => $this->remoteCategoriesForView(),
            'categoryMappings' => $this->categoryMappingsForView(),
            'localCategories' => $this->localCategoriesForView(),
            'stats' => $this->statsForView(),
            'activeFeedRun' => $this->activeFeedRunForView(),
        ];
    }

    public function selectSupplier(int $id): void
    {
        $this->selectedSupplierId = $id > 0 ? $id : null;
        $this->activeFeedRunId = null;
        $this->loadSelectedSupplier();
    }

    public function saveSupplier(): void
    {
        $this->validate([
            'supplierName' => ['required', 'string', 'max:255'],
            'apiUrl' => ['nullable', 'url', 'max:2000'],
            'feedType' => ['required', 'in:xml,csv,json'],
            'purchaseMarkupPercent' => ['nullable', 'numeric'],
            'saleMarkupPercent' => ['nullable', 'numeric'],
            'availableText' => ['nullable', 'string', 'max:255'],
            'unavailableText' => ['nullable', 'string', 'max:255'],
            'purchasePriceVatMode' => ['required', 'in:ex_vat,inc_vat'],
            'salePriceVatMode' => ['required', 'in:ex_vat,inc_vat'],
        ]);

        $data = [
            'name' => trim($this->supplierName),
            'slug' => $this->supplierSlug(trim($this->supplierName), $this->selectedSupplierId),
            'api_url' => trim($this->apiUrl) !== '' ? trim($this->apiUrl) : null,
            'product_data_url' => trim($this->productDataUrl) !== '' ? trim($this->productDataUrl) : null,
            'feed_type' => $this->feedType,
            'purchase_markup_percent' => $this->decimalOrZero($this->purchaseMarkupPercent),
            'sale_markup_percent' => $this->decimalOrZero($this->saleMarkupPercent),
            'available_text' => trim($this->availableText) !== '' ? trim($this->availableText) : null,
            'unavailable_text' => trim($this->unavailableText) !== '' ? trim($this->unavailableText) : null,
            'update_quantities' => $this->updateQuantities,
            'update_purchase_price' => $this->updatePurchasePrice,
            'update_sale_price' => $this->updateSalePrice,
            'purchase_price_vat_mode' => $this->purchasePriceVatMode,
            'sale_price_vat_mode' => $this->salePriceVatMode,
            'active' => $this->active,
            'updated_at' => now(),
        ];

        if ($this->selectedSupplierId) {
            DB::table('suppliers')->where('id', $this->selectedSupplierId)->update($data);
        } else {
            $data['created_at'] = now();
            $this->selectedSupplierId = (int) DB::table('suppliers')->insertGetId($data);
        }

        $this->loadSelectedSupplier();

        Notification::make()->title('Ο προμηθευτής αποθηκεύτηκε.')->success()->send();
    }

    public function addRemoteCategory(): void
    {
        if (! $this->selectedSupplierId) {
            return;
        }

        $name = trim($this->remoteCategoryName);

        if ($name === '') {
            return;
        }

        $code = trim($this->remoteCategoryCode) !== '' ? trim($this->remoteCategoryCode) : $name;

        DB::table('supplier_remote_categories')->updateOrInsert(
            ['supplier_id' => $this->selectedSupplierId, 'remote_code' => $code],
            [
                'remote_name' => $name,
                'last_seen_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        $this->remoteCategoryName = '';
        $this->remoteCategoryCode = '';
    }

    public function saveCategoryMapping(string $remoteCode, string $remoteName): void
    {
        if (! $this->selectedSupplierId) {
            return;
        }

        $localCategoryId = (int) ($this->localCategoryIds[$this->remoteCategoryInputKey($remoteCode)] ?? 0);

        if ($localCategoryId <= 0) {
            return;
        }

        DB::table('supplier_category_mappings')->updateOrInsert(
            ['supplier_id' => $this->selectedSupplierId, 'remote_code' => $remoteCode],
            [
                'remote_name' => $remoteName,
                'category_id' => $localCategoryId,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        $inputKey = $this->remoteCategoryInputKey($remoteCode);

        unset($this->localCategoryIds[$inputKey], $this->localCategorySearches[$inputKey]);
    }

    public function selectLocalCategory(string $inputKey, int $categoryId, string $label): void
    {
        if ($categoryId <= 0) {
            return;
        }

        $this->localCategoryIds[$inputKey] = $categoryId;
        $this->localCategorySearches[$inputKey] = $label;
    }

    public function deleteMapping(int $id): void
    {
        DB::table('supplier_category_mappings')->where('id', $id)->delete();
    }

    public function scanXmlCategories(): void
    {
        $this->scanFeedCategories();
    }

    public function scanFeedCategories(): void
    {
        $supplier = $this->selectedSupplier();

        if (! $supplier || trim((string) ($supplier->api_url ?? '')) === '') {
            Notification::make()->title('Δεν υπάρχει API URL για σάρωση.')->warning()->send();
            return;
        }

        $this->startFeedRun($supplier, 'scan_categories', [
            'supplier:scan-categories',
            (string) $supplier->slug,
        ], 'Η σάρωση feed ξεκίνησε.');
    }

    public function syncSelectedSupplierProducts(): void
    {
        $this->runSelectedSupplierSync(false);
    }

    public function syncSelectedSupplierProductData(): void
    {
        $supplier = $this->selectedSupplier();

        if (trim((string) ($supplier->product_data_url ?? '')) === '') {
            Notification::make()->title('Ορίστε πρώτα URL για product data στον προμηθευτή.')->warning()->send();
            return;
        }

        $this->runSelectedSupplierSync(true);
    }

    public function refreshFeedRun(): void
    {
        $run = $this->activeFeedRunForView();

        if (! $run || ! in_array($run->status, ['completed', 'failed'], true)) {
            return;
        }

        if ($run->status === 'completed') {
            Notification::make()->title('Η εργασία προμηθευτή ολοκληρώθηκε.')->success()->send();
        } else {
            Notification::make()->title('Η εργασία προμηθευτή απέτυχε.')->body((string) ($run->error ?? $run->message ?? ''))->danger()->send();
        }
    }

    protected function runSelectedSupplierSync(bool $productData): void
    {
        $supplier = $this->selectedSupplier();

        if (! $supplier) {
            Notification::make()->title('Δεν έχει επιλεγεί προμηθευτής.')->warning()->send();
            return;
        }

        $command = [
            'supplier:sync-products',
            (string) $supplier->slug,
        ];

        if ($productData) {
            $command[] = '--product-data';
            $command[] = '--sync-images';
        }

        $this->startFeedRun(
            $supplier,
            $productData ? 'sync_product_data' : 'sync_products',
            $command,
            $productData ? 'Το import product data + εικόνες ξεκίνησε.' : 'Το update τιμών/stock ξεκίνησε.',
        );
    }

    protected function startFeedRun(object $supplier, string $action, array $artisanArguments, string $successMessage): void
    {
        if (! Schema::hasTable('supplier_feed_runs')) {
            Notification::make()->title('Λείπει το table supplier_feed_runs. Τρέξτε migrations.')->danger()->send();
            return;
        }

        $runId = (int) DB::table('supplier_feed_runs')->insertGetId([
            'supplier_id' => (int) $supplier->id,
            'action' => $action,
            'status' => 'queued',
            'stage' => 'Αναμονή',
            'message' => 'Η εργασία μπήκε σε ουρά και ξεκινάει τώρα.',
            'log' => json_encode([[
                'time' => now()->format('H:i:s'),
                'stage' => 'Αναμονή',
                'message' => 'Η εργασία μπήκε σε ουρά και ξεκινάει τώρα.',
            ]], JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->activeFeedRunId = $runId;

        $command = array_merge([$this->phpCliBinary(), base_path('artisan')], $artisanArguments, ['--job-id=' . $runId]);
        $shellCommand = implode(' ', array_map('escapeshellarg', $command)) . ' > /dev/null 2>&1 &';
        exec($shellCommand);

        Notification::make()->title($successMessage)->body('Το status θα ανανεώνεται αυτόματα στη σελίδα.')->success()->send();
    }

    protected function phpCliBinary(): string
    {
        $candidate = rtrim((string) PHP_BINDIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'php';

        return is_file($candidate) ? $candidate : 'php';
    }

    protected function ensureConfiguredSuppliers(): void
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
                    'api_url' => $priceUrl,
                    'product_data_url' => trim((string) ($supplier['product_data_url'] ?? '')) ?: null,
                    'feed_type' => (string) ($supplier['feed_type'] ?? 'xml'),
                    'auth_type' => $supplier['auth_type'] ?? null,
                    'username' => $supplier['username'] ?? null,
                    'password' => $supplier['password'] ?? null,
                    'api_token' => $supplier['api_token'] ?? null,
                'purchase_markup_percent' => 0,
                'sale_markup_percent' => 24,
                'available_text' => 'Available from 4 to 10 days',
                'unavailable_text' => 'Upon order',
                'update_quantities' => true,
                'update_purchase_price' => true,
                'update_sale_price' => false,
                'purchase_price_vat_mode' => 'ex_vat',
                'sale_price_vat_mode' => 'ex_vat',
                'active' => (bool) ($supplier['active'] ?? true),
                'updated_at' => $now,
                'created_at' => $now,
                ],
            );
        }
    }

    protected function loadSelectedSupplier(): void
    {
        $supplier = $this->selectedSupplier();

        if (! $supplier) {
            return;
        }

        $this->supplierName = (string) $supplier->name;
        $this->apiUrl = (string) ($supplier->api_url ?? '');
        $this->productDataUrl = (string) ($supplier->product_data_url ?? '');
        $this->feedType = (string) ($supplier->feed_type ?? 'xml');
        $this->purchaseMarkupPercent = $this->decimalString($supplier->purchase_markup_percent ?? 0);
        $this->saleMarkupPercent = $this->decimalString($supplier->sale_markup_percent ?? 0);
        $this->availableText = (string) ($supplier->available_text ?? '');
        $this->unavailableText = (string) ($supplier->unavailable_text ?? '');
        $this->updateQuantities = (bool) ($supplier->update_quantities ?? true);
        $this->updatePurchasePrice = (bool) ($supplier->update_purchase_price ?? true);
        $this->updateSalePrice = (bool) ($supplier->update_sale_price ?? false);
        $this->purchasePriceVatMode = (string) ($supplier->purchase_price_vat_mode ?? 'ex_vat');
        $this->salePriceVatMode = (string) ($supplier->sale_price_vat_mode ?? 'ex_vat');
        $this->active = (bool) ($supplier->active ?? true);
    }

    protected function suppliersForView(): array
    {
        $remoteCounts = DB::table('supplier_remote_categories')
            ->select('supplier_id', DB::raw('count(*) as remote_categories_count'))
            ->groupBy('supplier_id');

        $mappingCounts = DB::table('supplier_category_mappings')
            ->select('supplier_id', DB::raw('count(*) as mappings_count'))
            ->groupBy('supplier_id');

        $query = DB::table('suppliers')
            ->leftJoinSub($remoteCounts, 'remote_counts', fn ($join) => $join->on('remote_counts.supplier_id', '=', 'suppliers.id'))
            ->leftJoinSub($mappingCounts, 'mapping_counts', fn ($join) => $join->on('mapping_counts.supplier_id', '=', 'suppliers.id'))
            ->select('suppliers.*', DB::raw('coalesce(remote_counts.remote_categories_count, 0) as remote_categories_count'), DB::raw('coalesce(mapping_counts.mappings_count, 0) as mappings_count'));

        if (trim($this->supplierSearch) !== '') {
            $like = '%' . trim($this->supplierSearch) . '%';
            $query->where(function ($builder) use ($like): void {
                $builder->where('suppliers.name', 'like', $like)
                    ->orWhere('suppliers.api_url', 'like', $like);
            });
        }

        return $query->orderByDesc('suppliers.active')->orderBy('suppliers.name')->get()->all();
    }

    protected function selectedSupplier(): ?object
    {
        return $this->selectedSupplierId
            ? DB::table('suppliers')->where('id', $this->selectedSupplierId)->first()
            : null;
    }

    protected function remoteCategoriesForView(): array
    {
        if (! $this->selectedSupplierId) {
            return [];
        }

        return DB::table('supplier_remote_categories')
            ->leftJoin('supplier_category_mappings', function ($join): void {
                $join->on('supplier_category_mappings.supplier_id', '=', 'supplier_remote_categories.supplier_id')
                    ->on('supplier_category_mappings.remote_code', '=', 'supplier_remote_categories.remote_code');
            })
            ->select('supplier_remote_categories.*', DB::raw('case when supplier_category_mappings.id is null then 0 else 1 end as is_mapped'))
            ->where('supplier_remote_categories.supplier_id', $this->selectedSupplierId)
            ->when(trim($this->remoteCategorySearch) !== '', function ($query): void {
                $terms = preg_split('/\s+/u', trim($this->remoteCategorySearch)) ?: [];

                $query->where(function ($builder) use ($terms): void {
                    foreach ($terms as $term) {
                        $like = '%' . $term . '%';

                        $builder->where(function ($inner) use ($like): void {
                            $inner->where('supplier_remote_categories.remote_name', 'like', $like)
                                ->orWhere('supplier_remote_categories.remote_code', 'like', $like);
                        });
                    }
                });
            })
            ->orderByDesc('is_mapped')
            ->orderBy('supplier_remote_categories.remote_name')
            ->limit(500)
            ->get()
            ->all();
    }

    protected function categoryMappingsForView(): array
    {
        if (! $this->selectedSupplierId) {
            return [];
        }

        return DB::table('supplier_category_mappings')
            ->leftJoin('categories', 'categories.id', '=', 'supplier_category_mappings.category_id')
            ->select('supplier_category_mappings.*', 'categories.full_path as local_path', 'categories.name as local_name')
            ->where('supplier_category_mappings.supplier_id', $this->selectedSupplierId)
            ->orderBy('supplier_category_mappings.remote_name')
            ->get()
            ->all();
    }

    protected function localCategoriesForView(): array
    {
        return DB::table('categories')
            ->select('id', 'name', 'full_path')
            ->where('active', true)
            ->orderBy('full_path')
            ->get()
            ->map(fn ($category): array => [
                'id' => (int) $category->id,
                'label' => (string) ($category->full_path ?: $category->name),
            ])
            ->all();
    }

    protected function statsForView(): array
    {
        return [
            'suppliers' => DB::table('suppliers')->count(),
            'active' => DB::table('suppliers')->where('active', true)->count(),
            'remote_categories' => Schema::hasTable('supplier_remote_categories') ? DB::table('supplier_remote_categories')->count() : 0,
            'mappings' => Schema::hasTable('supplier_category_mappings') ? DB::table('supplier_category_mappings')->count() : 0,
        ];
    }

    protected function activeFeedRunForView(): ?object
    {
        if (! Schema::hasTable('supplier_feed_runs')) {
            return null;
        }

        $query = DB::table('supplier_feed_runs')
            ->when($this->selectedSupplierId, fn ($builder) => $builder->where('supplier_id', $this->selectedSupplierId));

        if ($this->activeFeedRunId) {
            $run = (clone $query)->where('id', $this->activeFeedRunId)->first();
        } else {
            $run = (clone $query)->latest('id')->first();
        }

        if (! $run) {
            return null;
        }

        $run->log_entries = $run->log ? (json_decode((string) $run->log, true) ?: []) : [];

        return $run;
    }

    protected function extractCategoriesFromXml(string $xml): array
    {
        $categories = [];

        libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);

        if (! $document) {
            return [];
        }

        $categoryKeys = $this->categoryKeys();

        $leafNodes = $document->xpath('//*[not(*)]') ?: [];

        foreach ($leafNodes as $node) {
            $name = Str::lower((string) $node->getName());

            if (! $this->isCategoryKey($name, $categoryKeys)) {
                continue;
            }

            $value = $this->normaliseRemoteCategory((string) $node);

            if ($value !== '') {
                $categories[$value] = ($categories[$value] ?? 0) + 1;
            }
        }

        foreach ($document->xpath('//*[@*]') ?: [] as $node) {
            foreach ($node->attributes() ?: [] as $attribute => $attributeValue) {
                $name = Str::lower((string) $attribute);

                if (! $this->isCategoryKey($name, $categoryKeys)) {
                    continue;
                }

                $value = $this->normaliseRemoteCategory((string) $attributeValue);

                if ($value !== '') {
                    $categories[$value] = ($categories[$value] ?? 0) + 1;
                }
            }
        }

        ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        return $categories;
    }

    protected function extractCategoriesFromCsv(string $csv): array
    {
        $categories = [];
        $lines = preg_split('/\R/u', trim($csv)) ?: [];

        if ($lines === []) {
            return [];
        }

        $delimiter = $this->detectCsvDelimiter($lines[0]);
        $headers = array_map(
            fn (string $header): string => Str::lower(trim($header, " \t\n\r\0\x0B\"'")),
            str_getcsv($lines[0], $delimiter),
        );

        $categoryColumns = [];
        $categoryKeys = $this->categoryKeys();

        foreach ($headers as $index => $header) {
            if ($this->isCategoryKey($header, $categoryKeys)) {
                $categoryColumns[] = $index;
            }
        }

        if ($categoryColumns === []) {
            return [];
        }

        foreach (array_slice($lines, 1) as $line) {
            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line, $delimiter);
            $pathParts = [];

            foreach ($categoryColumns as $column) {
                $value = $this->normaliseRemoteCategory((string) ($row[$column] ?? ''));

                if ($value !== '') {
                    $pathParts[] = $value;
                }
            }

            if ($pathParts === []) {
                continue;
            }

            $path = implode(' / ', array_values(array_unique($pathParts)));
            $categories[$path] = ($categories[$path] ?? 0) + 1;
        }

        ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        return $categories;
    }

    protected function detectCsvDelimiter(string $headerLine): string
    {
        $delimiters = [',', ';', "\t", '|'];
        $bestDelimiter = ';';
        $bestCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = count(str_getcsv($headerLine, $delimiter));

            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    protected function categoryKeys(): array
    {
        return [
            'category',
            'categorie',
            'kategorie',
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
        ];
    }

    protected function isCategoryKey(string $name, array $categoryKeys): bool
    {
        $name = Str::lower(trim($name));

        if (preg_match('/^kat[1-6]$/', $name)) {
            return true;
        }

        if (preg_match('/^lwg[1-6]$/', $name)) {
            return true;
        }

        return collect($categoryKeys)->contains(fn (string $key): bool => str_contains($name, $key));
    }

    protected function normaliseRemoteCategory(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?: '';

        if ($value === '' || mb_strlen($value) > 255) {
            return '';
        }

        return $value;
    }

    protected function remoteCategoryInputKey(string $remoteCode): string
    {
        return md5($remoteCode);
    }

    protected function supplierSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'supplier';
        $slug = $base;
        $index = 2;

        while (DB::table('suppliers')
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $index++;
        }

        return $slug;
    }

    protected function decimalString($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    protected function decimalOrZero(string $value): float
    {
        $value = str_replace(',', '.', trim($value));

        return $value === '' ? 0.0 : (float) $value;
    }
}
