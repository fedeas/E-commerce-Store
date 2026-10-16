<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use XMLReader;
use ZipArchive;

class ImportCatalogProductsFromExcel extends Command
{
    protected $signature = 'catalog:import-products {file=storage/app/imports/products_export_all.xlsx} {--dry-run} {--limit=0} {--start=1}';

    protected $description = 'Import or update catalog products from products_export_all.xlsx';

    private array $columns = [];

    private array $brandCache = [];

    private array $supplierCache = [];

    private array $categoryCache = [];

    public function handle(): int
    {
        @ini_set('max_execution_time', '0');
        @ini_set('memory_limit', '1024M');

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        DB::disableQueryLog();


        if (! class_exists(ZipArchive::class) && ! $this->unzipAvailable()) {
            $this->error('ZipArchive is not available and command-line unzip was not found.');

            return self::FAILURE;
        }

        if (! class_exists(XMLReader::class)) {
            $this->error('XMLReader is not available.');

            return self::FAILURE;
        }

        foreach (['products', 'categories', 'brands', 'suppliers', 'product_prices'] as $table) {
            if (! Schema::hasTable($table)) {
                $this->error('Missing table: ' . $table);

                return self::FAILURE;
            }

            $this->columns[$table] = Schema::getColumnListing($table);
        }

        $file = $this->argument('file');
        $path = str_starts_with($file, '/') ? $file : base_path($file);

        if (! is_file($path)) {
            $this->error('File not found: ' . $path);

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $start = max(1, (int) $this->option('start'));

        $stats = [
            'rows_seen' => 0,
            'rows_processed' => 0,
            'rows_skipped' => 0,
            'products_created' => 0,
            'products_updated' => 0,
            'prices_created' => 0,
            'prices_updated' => 0,
            'brands_created' => 0,
            'suppliers_created' => 0,
            'categories_created' => 0,
        ];

        $rootStats = [];
        $brandStats = [];
        $supplierStats = [];

        $this->info('Reading: ' . $path);

        $this->processRows($path, function (array $row, int $rowNumber) use (&$stats, &$rootStats, &$brandStats, &$supplierStats, $dryRun, $limit, $start): bool {
            if ($rowNumber < $start) {
                return true;
            }

            if ($limit > 0 && $stats['rows_processed'] >= $limit) {
                return false;
            }

            $stats['rows_seen']++;

            $legacyId = $this->normalizeInteger($row['legacy_id'] ?? '');
            $code = $this->normalizeIdentifier($row['code'] ?? '');
            $mpn = $this->normalizeIdentifier($row['mpn'] ?? '');
            $ean = $this->normalizeIdentifier($row['ean'] ?? '');
            $title = $this->cleanText($row['title'] ?? '');
            $brandName = $this->cleanText($row['brand'] ?? '');
            $supplierName = $this->cleanText($row['supplier'] ?? '');
            $categoryPath = $this->normalizeCategoryPath($row['category'] ?? '');

            if ($title === '' && $code === '' && $mpn === '' && $ean === '') {
                $stats['rows_skipped']++;

                return true;
            }

            $stats['rows_processed']++;

            if ($categoryPath !== '') {
                $parts = $this->splitCategoryPath($categoryPath);

                if (count($parts) > 0) {
                    $rootStats[$parts[0]] = ($rootStats[$parts[0]] ?? 0) + 1;
                }
            }

            if ($brandName !== '') {
                $brandStats[$brandName] = ($brandStats[$brandName] ?? 0) + 1;
            }

            if ($supplierName !== '') {
                $supplierStats[$supplierName] = ($supplierStats[$supplierName] ?? 0) + 1;
            }

            if ($dryRun) {
                return true;
            }

            $brandResult = $this->findOrCreateBrand($brandName);
            $supplierResult = $this->findOrCreateSupplier($supplierName);
            $categoryResult = $this->findOrCreateCategoryPath($categoryPath);

            if ($brandResult['created']) {
                $stats['brands_created']++;
            }

            if ($supplierResult['created']) {
                $stats['suppliers_created']++;
            }

            $stats['categories_created'] += $categoryResult['created_count'];

            $productResult = $this->saveProduct([
                'legacy_id' => $legacyId,
                'code' => $code,
                'mpn' => $mpn,
                'ean' => $ean,
                'title' => $title,
                'brand_id' => $brandResult['id'],
                'category_id' => $categoryResult['id'],
                'supplier_id' => $supplierResult['id'],
                'category_path' => $categoryPath,
                'vat_rate' => $this->normalizeDecimal($row['vat'] ?? ''),
                'weight_kg' => $this->normalizeDecimal($row['weight'] ?? ''),
                'dimensions' => $this->cleanText($row['dimensions'] ?? ''),
                'meta_description' => $this->cleanText($row['meta_description'] ?? ''),
                'meta_keywords' => $this->cleanText($row['meta_keywords'] ?? ''),
            ]);

            if ($productResult['created']) {
                $stats['products_created']++;
            } else {
                $stats['products_updated']++;
            }

            $priceResult = $this->saveProductPrice((int) $productResult['id'], [
                'cost_ex_vat' => $this->normalizeDecimal($row['cost_ex_vat'] ?? ''),
                'retail_inc_vat' => $this->normalizeDecimal($row['retail_inc_vat'] ?? ''),
                'discount_inc_vat' => $this->normalizeDecimal($row['discount_inc_vat'] ?? ''),
                'skroutz_inc_vat' => $this->normalizeDecimal($row['skroutz_inc_vat'] ?? ''),
                'wholesale_inc_vat' => $this->normalizeDecimal($row['wholesale_inc_vat'] ?? ''),
                'silver_inc_vat' => $this->normalizeDecimal($row['silver_inc_vat'] ?? ''),
                'gold_inc_vat' => $this->normalizeDecimal($row['gold_inc_vat'] ?? ''),
            ]);

            if ($priceResult['created']) {
                $stats['prices_created']++;
            } else {
                $stats['prices_updated']++;
            }

            if ($stats['rows_processed'] % 1000 === 0) {
                $this->line('Processed: ' . $stats['rows_processed'] . ' | created: ' . $stats['products_created'] . ' | updated: ' . $stats['products_updated']);
            }

            return true;
        });

        arsort($rootStats);
        arsort($supplierStats);
        arsort($brandStats);

        $this->newLine();
        $this->table(
            ['Root Category', 'Products'],
            collect($rootStats)->take(20)->map(fn ($count, $name) => [$name, $count])->values()->all()
        );

        $this->newLine();
        $this->table(
            ['Supplier', 'Products'],
            collect($supplierStats)->take(20)->map(fn ($count, $name) => [$name, $count])->values()->all()
        );

        $this->newLine();
        $this->table(
            ['Brand', 'Products'],
            collect($brandStats)->take(20)->map(fn ($count, $name) => [$name, $count])->values()->all()
        );

        $this->newLine();

        foreach ($stats as $key => $value) {
            $this->info(str_replace('_', ' ', $key) . ': ' . $value);
        }

        if ($dryRun) {
            $this->info('Dry run completed. No database changes.');

            return self::SUCCESS;
        }

        $this->info('Product import completed.');

        return self::SUCCESS;
    }

    private function processRows(string $path, callable $callback): void
    {
        $sheetPath = $this->firstWorksheetPath($path);
        $sharedStrings = $this->sharedStrings($path);
        $sheetXml = $this->readZipEntry($path, $sheetPath);

        $reader = new XMLReader();

        if (! $reader->XML($sheetXml)) {
            throw new \RuntimeException('Could not open worksheet XML.');
        }

        $headerIndexes = null;
        $dataRowNumber = 0;

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
                continue;
            }

            $row = $this->parseRow($reader->readOuterXML(), $sharedStrings);

            if (count($row) === 0) {
                continue;
            }

            if ($headerIndexes === null) {
                $headerIndexes = $this->buildColumnIndexes($this->buildHeaderMap($row));

                if ($headerIndexes['title'] === null) {
                    $reader->close();

                    throw new \RuntimeException('Could not find product title column.');
                }

                continue;
            }

            $dataRowNumber++;
            $data = $this->extractProductRow($row, $headerIndexes);

            $continue = $callback($data, $dataRowNumber);

            if ($continue === false) {
                break;
            }
        }

        $reader->close();
    }

    private function firstWorksheetPath(string $path): string
    {
        $sheets = [];

        foreach ($this->zipEntries($path) as $name) {
            if (preg_match('/^xl\/worksheets\/sheet\d+\.xml$/', $name)) {
                $sheets[] = $name;
            }
        }

        natsort($sheets);

        $first = array_values($sheets)[0] ?? null;

        if (! $first) {
            throw new \RuntimeException('No worksheet found.');
        }

        return $first;
    }

    private function sharedStrings(string $path): array
    {
        $xml = $this->readZipEntry($path, 'xl/sharedStrings.xml', false);

        if ($xml === null) {
            return [];
        }

        $reader = new XMLReader();
        $reader->XML($xml);

        $strings = [];

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'si') {
                continue;
            }

            $node = simplexml_load_string($reader->readOuterXML());

            if (! $node) {
                $strings[] = '';
                continue;
            }

            $texts = $node->xpath('.//*[local-name()="t"]') ?: [];
            $parts = [];

            foreach ($texts as $text) {
                $parts[] = (string) $text;
            }

            $strings[] = implode('', $parts);
        }

        $reader->close();

        return $strings;
    }

    private function zipEntries(string $path): array
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();

            if ($zip->open($path) !== true) {
                throw new \RuntimeException('Could not open XLSX file.');
            }

            $entries = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);

                if (is_string($name)) {
                    $entries[] = $name;
                }
            }

            $zip->close();

            return $entries;
        }

        $output = shell_exec('unzip -Z -1 ' . escapeshellarg($path) . ' 2>/dev/null');

        if (! is_string($output) || trim($output) === '') {
            throw new \RuntimeException('Could not list XLSX entries with unzip.');
        }

        return array_values(array_filter(array_map('trim', preg_split('/\R/', $output) ?: [])));
    }

    private function readZipEntry(string $path, string $entry, bool $required = true): ?string
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();

            if ($zip->open($path) !== true) {
                throw new \RuntimeException('Could not open XLSX file.');
            }

            $contents = $zip->getFromName($entry);
            $zip->close();

            if ($contents !== false) {
                return $contents;
            }

            if (! $required) {
                return null;
            }

            throw new \RuntimeException('Missing XLSX entry: ' . $entry);
        }

        $output = shell_exec('unzip -p ' . escapeshellarg($path) . ' ' . escapeshellarg($entry) . ' 2>/dev/null');

        if (is_string($output) && $output !== '') {
            return $output;
        }

        if (! $required) {
            return null;
        }

        throw new \RuntimeException('Could not read XLSX entry: ' . $entry);
    }

    private function unzipAvailable(): bool
    {
        if (! function_exists('shell_exec')) {
            return false;
        }

        $path = shell_exec('command -v unzip 2>/dev/null');

        return is_string($path) && trim($path) !== '';
    }

    private function parseRow(string $rowXml, array $sharedStrings): array
    {
        $xml = simplexml_load_string($rowXml);

        if (! $xml) {
            return [];
        }

        $cells = $xml->xpath('.//*[local-name()="c"]') ?: [];
        $row = [];

        foreach ($cells as $cell) {
            $reference = (string) ($cell['r'] ?? '');

            if (! preg_match('/^([A-Z]+)/', $reference, $matches)) {
                continue;
            }

            $index = $this->columnIndex($matches[1]);
            $type = (string) ($cell['t'] ?? '');
            $value = '';

            if ($type === 'inlineStr') {
                $texts = $cell->xpath('.//*[local-name()="t"]') ?: [];
                $parts = [];

                foreach ($texts as $text) {
                    $parts[] = (string) $text;
                }

                $value = implode('', $parts);
            } else {
                $nodes = $cell->xpath('./*[local-name()="v"]') ?: [];
                $raw = isset($nodes[0]) ? (string) $nodes[0] : '';

                if ($type === 's') {
                    $value = $sharedStrings[(int) $raw] ?? '';
                } else {
                    $value = $raw;
                }
            }

            $row[$index] = $value;
        }

        return $row;
    }

    private function columnIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $number = 0;

        for ($i = 0; $i < strlen($letters); $i++) {
            $number = ($number * 26) + (ord($letters[$i]) - 64);
        }

        return $number - 1;
    }

    private function buildHeaderMap(array $row): array
    {
        $map = [];

        foreach ($row as $index => $value) {
            $header = $this->normalizeHeader((string) $value);

            if ($header !== '') {
                $map[$header] = $index;
            }
        }

        return $map;
    }

    private function buildColumnIndexes(array $headerMap): array
    {
        return [
            'legacy_id' => $this->headerIndex($headerMap, ['id']),
            'code' => $this->headerIndex($headerMap, ['κωδικος', 'code']),
            'mpn' => $this->headerIndex($headerMap, ['mpn']),
            'ean' => $this->headerIndex($headerMap, ['ean']),
            'title' => $this->headerIndex($headerMap, ['τιτλος', 'title'], [['τιτλος'], ['title']]),
            'brand' => $this->headerIndex($headerMap, ['κατασκευαστης', 'manufacturer', 'brand'], [['κατασκευαστης'], ['manufacturer']]),
            'supplier' => $this->headerIndex($headerMap, ['προμηθευτης', 'supplier'], [['προμηθευτης'], ['supplier']]),
            'category' => $this->headerIndex($headerMap, ['κατηγορια', 'category'], [['κατηγορια'], ['category']]),
            'vat' => $this->headerIndex($headerMap, ['φπα', 'vat']),
            'cost_ex_vat' => $this->headerIndex($headerMap, ['τιμηαγοραςχωρισφπα', 'costexvat'], [['τιμη', 'αγορας'], ['cost']]),
            'retail_inc_vat' => $this->headerIndex($headerMap, ['τιμηπωλησηςμεφπα', 'retailincvat'], [['τιμη', 'πωλησης'], ['retail']]),
            'discount_inc_vat' => $this->headerIndex($headerMap, ['τιμηεκπτωσηςμεφπα', 'discountincvat'], [['εκπτωσης'], ['discount']]),
            'skroutz_inc_vat' => $this->headerIndex($headerMap, ['τιμηskroutzμεφπα', 'skroutzincvat'], [['skroutz']]),
            'wholesale_inc_vat' => $this->headerIndex($headerMap, ['χονδρικη', 'wholesale'], [['χονδρικη'], ['wholesale']]),
            'silver_inc_vat' => $this->headerIndex($headerMap, ['sliver', 'silver']),
            'gold_inc_vat' => $this->headerIndex($headerMap, ['gold']),
            'weight' => $this->headerIndex($headerMap, ['βαροςkg', 'weightkg'], [['βαρος'], ['weight']]),
            'dimensions' => $this->headerIndex($headerMap, ['dimensions'], [['διαστασεις'], ['dimensions']]),
            'meta_description' => $this->headerIndex($headerMap, ['metadesc', 'metadescription'], [['meta', 'desc']]),
            'meta_keywords' => $this->headerIndex($headerMap, ['metakeywords'], [['meta', 'keywords']]),
        ];
    }

    private function headerIndex(array $headerMap, array $exactCandidates, array $containsCandidates = []): ?int
    {
        foreach ($exactCandidates as $candidate) {
            if (array_key_exists($candidate, $headerMap)) {
                return $headerMap[$candidate];
            }
        }

        foreach ($containsCandidates as $terms) {
            foreach ($headerMap as $header => $index) {
                $matched = true;

                foreach ($terms as $term) {
                    if (! str_contains($header, $term)) {
                        $matched = false;
                        break;
                    }
                }

                if ($matched) {
                    return $index;
                }
            }
        }

        return null;
    }

    private function extractProductRow(array $row, array $indexes): array
    {
        $data = [];

        foreach ($indexes as $key => $index) {
            $data[$key] = $index === null ? '' : (string) ($row[$index] ?? '');
        }

        return $data;
    }

    private function normalizeHeader(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');

        $value = strtr($value, [
            'ά' => 'α',
            'έ' => 'ε',
            'ή' => 'η',
            'ί' => 'ι',
            'ό' => 'ο',
            'ύ' => 'υ',
            'ώ' => 'ω',
            'ϊ' => 'ι',
            'ΐ' => 'ι',
            'ϋ' => 'υ',
            'ΰ' => 'υ',
        ]);

        $value = preg_replace('/[^a-z0-9α-ω]+/u', '', $value);

        return $value ?: '';
    }

    private function saveProduct(array $row): array
    {
        $existingId = $this->findExistingProductId(
            legacyId: $row['legacy_id'],
            code: $row['code'],
            mpn: $row['mpn'],
            ean: $row['ean']
        );

        $data = [];

        if ($this->hasColumn('products', 'legacy_id')) {
            $data['legacy_id'] = $row['legacy_id'];
        }

        if ($this->hasColumn('products', 'code')) {
            $data['code'] = $this->emptyToNull($row['code']);
        }

        if ($this->hasColumn('products', 'mpn')) {
            $data['mpn'] = $this->emptyToNull($row['mpn']);
        }

        if ($this->hasColumn('products', 'ean')) {
            $data['ean'] = $this->emptyToNull($row['ean']);
        }

        if ($this->hasColumn('products', 'title') && $row['title'] !== '') {
            $data['title'] = $row['title'];
        }

        if ($this->hasColumn('products', 'slug')) {
            $slugBase = trim(($row['title'] ?: 'product') . ' ' . ($row['legacy_id'] ?: $row['code']));
            $data['slug'] = $this->uniqueSlug('products', $slugBase, $existingId);
        }

        if ($this->hasColumn('products', 'brand_id')) {
            $data['brand_id'] = $row['brand_id'];
        }

        if ($this->hasColumn('products', 'category_id')) {
            $data['category_id'] = $row['category_id'];
        }

        if ($this->hasColumn('products', 'primary_supplier_id')) {
            $data['primary_supplier_id'] = $row['supplier_id'];
        }

        $active = $this->isActivePath($this->splitCategoryPath($row['category_path']));

        if ($this->hasColumn('products', 'active')) {
            $data['active'] = $active;
        }

        if ($this->hasColumn('products', 'status')) {
            $data['status'] = $active ? 'active' : 'inactive';
        }

        if ($this->hasColumn('products', 'vat_rate')) {
            $data['vat_rate'] = $row['vat_rate'];
        }

        if ($this->hasColumn('products', 'weight_kg')) {
            $data['weight_kg'] = $row['weight_kg'];
        }

        if ($this->hasColumn('products', 'dimensions')) {
            $data['dimensions'] = $this->emptyToNull($row['dimensions']);
        }

        if ($this->hasColumn('products', 'meta_description')) {
            $data['meta_description'] = $this->emptyToNull($row['meta_description']);
        }

        if ($this->hasColumn('products', 'meta_keywords')) {
            $data['meta_keywords'] = $this->emptyToNull($row['meta_keywords']);
        }

        if ($this->hasColumn('products', 'last_imported_at')) {
            $data['last_imported_at'] = now();
        }

        if ($this->hasColumn('products', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if ($existingId) {
            DB::table('products')
                ->where('id', $existingId)
                ->update($data);

            return [
                'id' => $existingId,
                'created' => false,
            ];
        }

        if ($this->hasColumn('products', 'title') && ! array_key_exists('title', $data)) {
            $data['title'] = 'Product ' . ($row['legacy_id'] ?: $row['code'] ?: $row['mpn'] ?: $row['ean'] ?: Str::random(8));
        }

        if ($this->hasColumn('products', 'created_at')) {
            $data['created_at'] = now();
        }

        $id = DB::table('products')->insertGetId($data);

        return [
            'id' => (int) $id,
            'created' => true,
        ];
    }

    private function findExistingProductId(?int $legacyId, string $code, string $mpn, string $ean): ?int
    {
        if ($legacyId === null || ! $this->hasColumn('products', 'legacy_id')) {
            return null;
        }

        $id = DB::table('products')
            ->where('legacy_id', $legacyId)
            ->value('id');

        return $id ? (int) $id : null;
    }

    private function saveProductPrice(int $productId, array $prices): array
    {
        if (! $this->hasColumn('product_prices', 'product_id')) {
            return [
                'created' => false,
            ];
        }

        $existingId = DB::table('product_prices')
            ->where('product_id', $productId)
            ->value('id');

        $data = [
            'product_id' => $productId,
        ];

        foreach ($prices as $column => $value) {
            if ($this->hasColumn('product_prices', $column)) {
                $data[$column] = $value;
            }
        }

        if ($this->hasColumn('product_prices', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if ($existingId) {
            DB::table('product_prices')
                ->where('id', $existingId)
                ->update($data);

            return [
                'created' => false,
            ];
        }

        if ($this->hasColumn('product_prices', 'created_at')) {
            $data['created_at'] = now();
        }

        DB::table('product_prices')->insert($data);

        return [
            'created' => true,
        ];
    }

    private function findOrCreateBrand(string $name): array
    {
        if ($name === '') {
            return [
                'id' => null,
                'created' => false,
            ];
        }

        $key = mb_strtolower($name, 'UTF-8');

        if (array_key_exists($key, $this->brandCache)) {
            return [
                'id' => $this->brandCache[$key],
                'created' => false,
            ];
        }

        $existing = DB::table('brands')
            ->where('name', $name)
            ->first();

        if ($existing) {
            $this->brandCache[$key] = (int) $existing->id;

            return [
                'id' => (int) $existing->id,
                'created' => false,
            ];
        }

        $data = [];

        if ($this->hasColumn('brands', 'name')) {
            $data['name'] = $name;
        }

        if ($this->hasColumn('brands', 'slug')) {
            $data['slug'] = $this->uniqueSlug('brands', $name);
        }

        if ($this->hasColumn('brands', 'active')) {
            $data['active'] = true;
        }

        if ($this->hasColumn('brands', 'created_at')) {
            $data['created_at'] = now();
        }

        if ($this->hasColumn('brands', 'updated_at')) {
            $data['updated_at'] = now();
        }

        $id = DB::table('brands')->insertGetId($data);
        $this->brandCache[$key] = (int) $id;

        return [
            'id' => (int) $id,
            'created' => true,
        ];
    }

    private function findOrCreateSupplier(string $name): array
    {
        if ($name === '') {
            return [
                'id' => null,
                'created' => false,
            ];
        }

        $key = mb_strtolower($name, 'UTF-8');

        if (array_key_exists($key, $this->supplierCache)) {
            return [
                'id' => $this->supplierCache[$key],
                'created' => false,
            ];
        }

        $existing = DB::table('suppliers')
            ->where('name', $name)
            ->first();

        if ($existing) {
            $this->supplierCache[$key] = (int) $existing->id;

            return [
                'id' => (int) $existing->id,
                'created' => false,
            ];
        }

        $data = [];

        if ($this->hasColumn('suppliers', 'name')) {
            $data['name'] = $name;
        }

        if ($this->hasColumn('suppliers', 'slug')) {
            $data['slug'] = $this->uniqueSlug('suppliers', $name);
        }

        if ($this->hasColumn('suppliers', 'active')) {
            $data['active'] = true;
        }

        if ($this->hasColumn('suppliers', 'created_at')) {
            $data['created_at'] = now();
        }

        if ($this->hasColumn('suppliers', 'updated_at')) {
            $data['updated_at'] = now();
        }

        $id = DB::table('suppliers')->insertGetId($data);
        $this->supplierCache[$key] = (int) $id;

        return [
            'id' => (int) $id,
            'created' => true,
        ];
    }

    private function findOrCreateCategoryPath(string $path): array
    {
        $path = $this->normalizeCategoryPath($path);

        if ($path === '') {
            return [
                'id' => null,
                'created_count' => 0,
            ];
        }

        if (array_key_exists($path, $this->categoryCache)) {
            return [
                'id' => $this->categoryCache[$path],
                'created_count' => 0,
            ];
        }

        $parts = $this->splitCategoryPath($path);
        $parentId = null;
        $pathParts = [];
        $createdCount = 0;

        foreach ($parts as $level => $name) {
            $pathParts[] = $name;
            $fullPath = implode(' > ', $pathParts);

            $query = DB::table('categories');

            if ($this->hasColumn('categories', 'parent_id')) {
                if ($parentId === null) {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $parentId);
                }

                $query->where('name', $name);
            } elseif ($this->hasColumn('categories', 'full_path')) {
                $query->where('full_path', $fullPath);
            } else {
                $query->where('name', $name);
            }

            $existing = $query->first();

            if ($existing) {
                $parentId = (int) $existing->id;
                continue;
            }

            $data = [];

            if ($this->hasColumn('categories', 'name')) {
                $data['name'] = $name;
            }

            if ($this->hasColumn('categories', 'parent_id')) {
                $data['parent_id'] = $parentId;
            }

            if ($this->hasColumn('categories', 'slug')) {
                $data['slug'] = $this->uniqueSlug('categories', $fullPath);
            }

            if ($this->hasColumn('categories', 'full_path')) {
                $data['full_path'] = $fullPath;
            }

            if ($this->hasColumn('categories', 'level')) {
                $data['level'] = $level;
            }

            if ($this->hasColumn('categories', 'sort_order')) {
                $data['sort_order'] = $this->nextCategorySortOrder($parentId);
            }

            $active = $this->isActivePath($parts);

            if ($this->hasColumn('categories', 'active')) {
                $data['active'] = $active;
            }

            if ($this->hasColumn('categories', 'status')) {
                $data['status'] = $active ? 'active' : 'inactive';
            }

            if ($this->hasColumn('categories', 'filters_enabled')) {
                $data['filters_enabled'] = $active;
            }

            foreach (['xml_feed_enabled', 'xml_only_available', 'boxnow_enabled', 'article_11_enabled'] as $column) {
                if ($this->hasColumn('categories', $column)) {
                    $data[$column] = false;
                }
            }

            if ($this->hasColumn('categories', 'created_at')) {
                $data['created_at'] = now();
            }

            if ($this->hasColumn('categories', 'updated_at')) {
                $data['updated_at'] = now();
            }

            $parentId = (int) DB::table('categories')->insertGetId($data);
            $createdCount++;
        }

        $this->categoryCache[$path] = $parentId;

        return [
            'id' => $parentId,
            'created_count' => $createdCount,
        ];
    }

    private function nextCategorySortOrder(?int $parentId): int
    {
        if (! $this->hasColumn('categories', 'sort_order')) {
            return 10001;
        }

        $query = DB::table('categories');

        if ($this->hasColumn('categories', 'parent_id')) {
            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        $max = $query->max('sort_order');

        return $max ? ((int) $max + 2) : 10001;
    }

    private function splitCategoryPath(string $path): array
    {
        $path = $this->normalizeCategoryPath($path);
        $parts = preg_split('/\s*>\s*/u', $path) ?: [];

        $parts = array_map(function (string $part): string {
            $part = preg_replace('/\s+/u', ' ', $part);

            return trim($part);
        }, $parts);

        return array_values(array_filter($parts, fn (string $part): bool => $part !== ''));
    }

    private function normalizeCategoryPath(string $value): string
    {
        $value = $this->cleanText($value);
        $value = trim($value, ' >');

        return $value;
    }

    private function isActivePath(array $parts): bool
    {
        $path = mb_strtolower(implode(' ', $parts), 'UTF-8');

        foreach (['dont enable', 'do not enable', 'test', 'κουβας', 'κουβασ', 'koyvas'] as $word) {
            if (str_contains($path, $word)) {
                return false;
            }
        }

        return true;
    }

    private function normalizeIdentifier(string $value): string
    {
        $value = $this->cleanText($value);

        if (preg_match('/^\d+\.0+$/', $value)) {
            $value = preg_replace('/\.0+$/', '', $value);
        }

        if (preg_match('/^\d+(\.\d+)?e\+\d+$/i', $value)) {
            $value = number_format((float) $value, 0, '', '');
        }

        return $value;
    }

    private function normalizeInteger(string $value): ?int
    {
        $value = $this->normalizeIdentifier($value);

        if ($value === '') {
            return null;
        }

        return preg_match('/^\d+$/', $value) ? (int) $value : null;
    }

    private function normalizeDecimal(string $value): ?float
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(['€', ' '], '', $value);
        $value = preg_replace('/[^\d,.\-]/', '', $value);

        if ($value === '' || $value === '-' || $value === ',' || $value === '.') {
            return null;
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($lastComma !== false) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function cleanText(string $value): string
    {
        $value = str_replace(["\r", "\n", "\t"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return trim($value);
    }

    private function emptyToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function uniqueSlug(string $table, string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value);

        if ($base === '') {
            $base = $table . '-' . substr(md5($value . microtime(true)), 0, 12);
        }

        $slug = $base;
        $counter = 2;

        while ($this->slugExists($table, $slug, $ignoreId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $table, string $slug, ?int $ignoreId = null): bool
    {
        if (! $this->hasColumn($table, 'slug')) {
            return false;
        }

        $query = DB::table($table)->where('slug', $slug);

        if ($ignoreId !== null && $this->hasColumn($table, 'id')) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function hasColumn(string $table, string $column): bool
    {
        return in_array($column, $this->columns[$table] ?? [], true);
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $item) {
            $path = $directory . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }
}
