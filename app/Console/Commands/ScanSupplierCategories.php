<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ScanSupplierCategories extends Command
{
    protected $signature = 'supplier:scan-categories {supplier=all} {--job-id=}';

    protected $description = 'Scan a supplier feed and update remote bridge categories.';

    private ?int $jobId = null;

    public function handle(): int
    {
        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', '0');

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $this->jobId = (int) $this->option('job-id') ?: null;
        $supplierArgument = (string) $this->argument('supplier');
        $this->ensureConfiguredSuppliers();

        if ($supplierArgument === 'all') {
            $suppliers = DB::table('suppliers')
                ->where('active', true)
                ->orderBy('name')
                ->get(['slug', 'name']);

            if ($suppliers->isEmpty()) {
                $this->warn('No active suppliers found.');

                return self::SUCCESS;
            }

            $failed = 0;

            foreach ($suppliers as $supplier) {
                $this->info('Scanning supplier categories: ' . $supplier->name);

                if ($this->call('supplier:scan-categories', ['supplier' => (string) $supplier->slug]) !== self::SUCCESS) {
                    $failed++;
                }
            }

            return $failed > 0 ? self::FAILURE : self::SUCCESS;
        }

        try {
            $supplier = DB::table('suppliers')
                ->where('slug', $supplierArgument)
                ->orWhere('id', (int) $supplierArgument)
                ->first();

            if (! $supplier) {
                throw new \RuntimeException('Supplier not found: ' . $supplierArgument);
            }

            $url = trim((string) ($supplier->api_url ?? ''));
            $feedType = strtolower((string) ($supplier->feed_type ?? 'xml'));

            if ($url === '') {
                throw new \RuntimeException('Missing supplier API URL.');
            }

            $this->updateRun('running', 'Σύνδεση', 'Άνοιγμα σύνδεσης προς το feed.', [
                'supplier_id' => (int) $supplier->id,
                'started_at' => now(),
            ]);

            if ($feedType === 'json') {
                $this->scanJsonCategories($supplier);

                return self::SUCCESS;
            }

            $client = Http::timeout(600)->connectTimeout(25)->retry(1, 1500);

            if (($supplier->auth_type ?? null) === 'basic' && trim((string) ($supplier->username ?? '')) !== '') {
                $client = $client->withBasicAuth((string) $supplier->username, (string) ($supplier->password ?? ''));
            }

            $this->updateRun('running', 'Download feed', 'Κατεβαίνει το feed. Σε μεγάλο XML αυτό μπορεί να κρατήσει αρκετά λεπτά.');
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
                $response = $client->get($url, $this->queryCredentials($supplier));
                $response->throw();
                $body = $response->body();
            } catch (\Throwable $downloadException) {
                $this->updateRun('running', 'Download fallback', 'Το Laravel HTTP download απέτυχε. Δοκιμή με curl fallback.');
                $body = $this->downloadWithCurl($url, $supplier);
            }

            $this->updateRun('running', 'Ανάλυση feed', 'Το feed κατέβηκε (' . number_format(strlen($body) / 1024, 1) . ' KB). Γίνεται ανάγνωση κατηγοριών.');

            $categories = $feedType === 'csv'
                ? $this->extractCategoriesFromCsv($body)
                : $this->extractCategoriesFromXml($body);

            if ($categories === []) {
                $this->updateRun('running', '0 categories debug', $this->feedDebugMessage($body));
            }

            $this->updateRun('running', 'Αποθήκευση', 'Γράφονται οι remote κατηγορίες στη γέφυρα.', [
                'categories_found' => count($categories),
            ]);

            foreach ($categories as $category => $productsCount) {
                $remoteCode = is_array($productsCount) ? (string) ($productsCount['code'] ?? $category) : (string) $category;
                $remoteName = is_array($productsCount) ? (string) ($productsCount['name'] ?? $remoteCode) : (string) $category;
                $count = is_array($productsCount) ? (int) ($productsCount['count'] ?? 0) : (int) $productsCount;

                DB::table('supplier_remote_categories')->updateOrInsert(
                    ['supplier_id' => $supplier->id, 'remote_code' => $remoteCode],
                    [
                        'remote_name' => $remoteName,
                        'products_count' => $count,
                        'last_seen_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }

            $this->deleteExcludedRemoteCategories((int) $supplier->id);

            DB::table('suppliers')->where('id', $supplier->id)->update([
                'last_bridge_sync_at' => now(),
                'updated_at' => now(),
            ]);

            $this->updateRun('completed', 'Ολοκληρώθηκε', 'Βρέθηκαν ' . count($categories) . ' κατηγορίες από το feed.', [
                'categories_found' => count($categories),
                'completed_at' => now(),
            ]);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->updateRun('failed', 'Σφάλμα', 'Η σάρωση feed απέτυχε.', [
                'error' => $exception->getMessage(),
                'completed_at' => now(),
            ]);

            $this->error($exception->getMessage());

            return self::FAILURE;
        }
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

    private function scanJsonCategories(object $supplier): void
    {
        $url = trim((string) ($supplier->product_data_url ?? ''))
            ?: preg_replace('~/products(\?.*)?$~i', '/categories', trim((string) ($supplier->api_url ?? '')));

        if (! $url) {
            throw new \RuntimeException('Missing JSON categories URL.');
        }

        $this->updateRun('running', 'Download categories', 'Κατεβαίνουν οι κατηγορίες JSON από τον προμηθευτή.');

        $response = $this->jsonClient($supplier)->get($url, $this->queryCredentials($supplier));
        $response->throw();

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new \RuntimeException('The categories response is not valid JSON.');
        }

        if (($payload['success'] ?? true) === false) {
            throw new \RuntimeException((string) data_get($payload, 'error.message', 'Supplier returned an error.'));
        }

        $categories = $this->extractCategoriesFromJson($payload);

        $this->updateRun('running', 'Αποθήκευση', 'Γράφονται οι remote JSON κατηγορίες στη γέφυρα.', [
            'categories_found' => count($categories),
        ]);

        foreach ($categories as $category) {
            DB::table('supplier_remote_categories')->updateOrInsert(
                ['supplier_id' => $supplier->id, 'remote_code' => $category['code']],
                [
                    'remote_name' => $category['name'],
                    'products_count' => $category['count'],
                    'last_seen_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        $this->deleteExcludedRemoteCategories((int) $supplier->id);

        DB::table('suppliers')->where('id', $supplier->id)->update([
            'last_bridge_sync_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updateRun('completed', 'Ολοκληρώθηκε', 'Βρέθηκαν ' . count($categories) . ' κατηγορίες JSON.', [
            'categories_found' => count($categories),
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

        $credentials = [];

        if (trim((string) ($supplier->username ?? '')) !== '') {
            $credentials['user'] = (string) $supplier->username;
        }

        if (trim((string) ($supplier->password ?? '')) !== '') {
            $credentials['pass'] = (string) $supplier->password;
        }

        return array_merge($credentials, $extra);
    }

    private function extractCategoriesFromJson(array $payload): array
    {
        $root = data_get($payload, 'data.categories', data_get($payload, 'categories', data_get($payload, 'data', [])));
        $categories = [];

        $this->walkJsonCategories(is_array($root) ? $root : [], [], $categories);

        ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        return array_values($categories);
    }

    private function walkJsonCategories(array $nodes, array $parents, array &$categories): void
    {
        foreach ($nodes as $node) {
            if (! is_array($node)) {
                continue;
            }

            $id = $node['id'] ?? $node['category_id'] ?? $node['code'] ?? $node['api_code'] ?? null;
            $name = $this->normaliseRemoteCategory((string) ($node['name'] ?? $node['title'] ?? $node['description'] ?? ''));

            if ($id !== null && $name !== '') {
                $path = implode(' / ', array_merge($parents, [$name]));
                $categories[(string) $id] = [
                    'code' => (string) $id,
                    'name' => $path,
                    'count' => (int) ($node['products_count'] ?? $node['product_count'] ?? $node['count'] ?? 0),
                ];
            }

            foreach (['children', 'subcategories', 'categories', 'items'] as $childKey) {
                if (isset($node[$childKey]) && is_array($node[$childKey])) {
                    $this->walkJsonCategories($node[$childKey], $name !== '' ? array_merge($parents, [$name]) : $parents, $categories);
                }
            }
        }
    }

    private function downloadMessage(float $downloaded, float $total): string
    {
        $downloadedMb = number_format($downloaded / 1024 / 1024, 2);

        if ($total > 0) {
            return 'Κατεβαίνει το feed: ' . $downloadedMb . ' MB από ' . number_format($total / 1024 / 1024, 2) . ' MB.';
        }

        return 'Κατεβαίνει το feed: ' . $downloadedMb . ' MB μέχρι τώρα.';
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

    private function feedDebugMessage(string $body): string
    {
        $bytes = strlen($body);
        $preview = preg_replace('/\s+/u', ' ', trim(substr($body, 0, 300))) ?: '';
        $root = 'unknown';

        libxml_use_internal_errors(true);
        $document = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($document) {
            $root = (string) $document->getName();
        }

        if ($preview === '') {
            $preview = 'empty response';
        }

        return 'Δεν βρέθηκαν categories. Download: ' . number_format($bytes / 1024, 1) . ' KB, XML root: ' . $root . ', αρχή response: ' . Str::limit($preview, 250, '');
    }

    private function extractCategoriesFromXml(string $xml): array
    {
        $categories = [];

        libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (! $document) {
            return [];
        }

        $categoryKeys = $this->categoryKeys();

        foreach ($document->xpath('//*[not(*)]') ?: [] as $node) {
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

    private function extractCategoriesFromCsv(string $csv): array
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

            if ($pathParts !== []) {
                $path = implode(' / ', array_values(array_unique($pathParts)));
                $categories[$path] = ($categories[$path] ?? 0) + 1;
            }
        }

        ksort($categories, SORT_NATURAL | SORT_FLAG_CASE);

        return $categories;
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

    private function categoryKeys(): array
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

    private function isCategoryKey(string $name, array $categoryKeys): bool
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

    private function normaliseRemoteCategory(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?: '';

        if ($value === '' || mb_strlen($value) > 255) {
            return '';
        }

        if ($this->isExcludedRemoteCategoryLabel($value)) {
            return '';
        }

        return $value;
    }

    private function deleteExcludedRemoteCategories(int $supplierId): void
    {
        $ids = DB::table('supplier_remote_categories')
            ->leftJoin('supplier_category_mappings', function ($join): void {
                $join->on('supplier_category_mappings.supplier_id', '=', 'supplier_remote_categories.supplier_id')
                    ->on('supplier_category_mappings.remote_code', '=', 'supplier_remote_categories.remote_code');
            })
            ->where('supplier_remote_categories.supplier_id', $supplierId)
            ->whereNull('supplier_category_mappings.id')
            ->get([
                'supplier_remote_categories.id',
                'supplier_remote_categories.remote_code',
                'supplier_remote_categories.remote_name',
            ])
            ->filter(fn ($row): bool => $this->isExcludedRemoteCategoryLabel((string) $row->remote_name)
                || $this->isExcludedRemoteCategoryLabel((string) $row->remote_code))
            ->pluck('id')
            ->all();

        foreach (array_chunk($ids, 500) as $chunk) {
            DB::table('supplier_remote_categories')->whereIn('id', $chunk)->delete();
        }
    }

    private function isExcludedRemoteCategoryLabel(string $value): bool
    {
        $value = trim($value);

        return $value !== '' && preg_match('/^\d+$/', $value) === 1;
    }

}
