<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use XMLReader;
use ZipArchive;

class ImportCatalogCategoriesFromExcel extends Command
{
    protected $signature = 'catalog:import-categories {file=storage/app/imports/products_export_all.xlsx} {--dry-run}';

    protected $description = 'Import catalog categories from products_export_all.xlsx';

    private array $columns = [];

    public function handle(): int
    {
        if (! class_exists(ZipArchive::class)) {
            $this->error('ZipArchive is not available.');

            return self::FAILURE;
        }

        if (! class_exists(XMLReader::class)) {
            $this->error('XMLReader is not available.');

            return self::FAILURE;
        }

        if (! Schema::hasTable('categories')) {
            $this->error('The categories table does not exist.');

            return self::FAILURE;
        }

        $file = $this->argument('file');
        $path = str_starts_with($file, '/') ? $file : base_path($file);

        if (! is_file($path)) {
            $this->error('File not found: ' . $path);

            return self::FAILURE;
        }

        $this->columns = Schema::getColumnListing('categories');

        $this->info('Reading: ' . $path);

        $paths = $this->readCategoryPaths($path);

        if (count($paths) === 0) {
            $this->error('No category paths found.');

            return self::FAILURE;
        }

        $rootStats = [];

        foreach ($paths as $categoryPath => $count) {
            $parts = $this->splitCategoryPath($categoryPath);

            if (count($parts) === 0) {
                continue;
            }

            $rootStats[$parts[0]] = ($rootStats[$parts[0]] ?? 0) + $count;
        }

        arsort($rootStats);

        $this->table(
            ['Root Category', 'Products'],
            collect($rootStats)->map(fn ($count, $name) => [$name, $count])->values()->all()
        );

        $this->info('Unique category paths: ' . count($paths));

        if ($this->option('dry-run')) {
            $this->info('Dry run completed. No database changes.');

            return self::SUCCESS;
        }

        $created = 0;
        $updated = 0;
        $processed = 0;
        $sortCounters = [];

        DB::transaction(function () use ($paths, &$created, &$updated, &$processed, &$sortCounters): void {
            foreach (array_keys($paths) as $categoryPath) {
                $parts = $this->splitCategoryPath($categoryPath);

                if (count($parts) === 0) {
                    continue;
                }

                $parentId = null;
                $pathParts = [];

                foreach ($parts as $level => $name) {
                    $pathParts[] = $name;
                    $fullPath = implode(' > ', $pathParts);
                    $parentKey = $parentId === null ? 'root' : (string) $parentId;

                    if (! array_key_exists($parentKey, $sortCounters)) {
                        $sortCounters[$parentKey] = $this->nextSortOrder($parentId);
                    }

                    $sortOrder = $sortCounters[$parentKey];
                    $sortCounters[$parentKey] += 2;

                    $result = $this->saveCategory(
                        name: $name,
                        parentId: $parentId,
                        fullPath: $fullPath,
                        level: $level,
                        sortOrder: $sortOrder,
                        active: $this->isActivePath($parts)
                    );

                    $parentId = $result['id'];

                    if ($result['created']) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }

                $processed++;
            }
        });

        $this->info('Category paths processed: ' . $processed);
        $this->info('Categories created: ' . $created);
        $this->info('Categories updated: ' . $updated);
        $this->info('Import completed.');

        return self::SUCCESS;
    }

    private function readCategoryPaths(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Could not open XLSX file.');
        }

        $sheetPath = $this->firstWorksheetPath($zip);
        $sharedStrings = $this->sharedStrings($zip);

        $tempDir = storage_path('app/tmp_import_' . Str::random(12));
        mkdir($tempDir, 0775, true);

        $zip->extractTo($tempDir, [$sheetPath]);
        $zip->close();

        $sheetFile = $tempDir . '/' . $sheetPath;

        $reader = new XMLReader();

        if (! $reader->open($sheetFile)) {
            $this->removeDirectory($tempDir);

            throw new \RuntimeException('Could not open worksheet XML.');
        }

        $headerMap = [];
        $categoryIndex = null;
        $paths = [];

        while ($reader->read()) {
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
                continue;
            }

            $rowXml = $reader->readOuterXML();
            $row = $this->parseRow($rowXml, $sharedStrings);

            if (count($row) === 0) {
                continue;
            }

            if (count($headerMap) === 0) {
                $headerMap = $this->buildHeaderMap($row);
                $categoryIndex = $this->categoryColumnIndex($headerMap);

                if ($categoryIndex === null) {
                    $reader->close();
                    $this->removeDirectory($tempDir);

                    throw new \RuntimeException('Could not find Κατηγορία column.');
                }

                continue;
            }

            $categoryPath = trim((string) ($row[$categoryIndex] ?? ''));

            if ($categoryPath === '') {
                continue;
            }

            $categoryPath = $this->normalizeCategoryPath($categoryPath);

            if ($categoryPath === '') {
                continue;
            }

            $paths[$categoryPath] = ($paths[$categoryPath] ?? 0) + 1;
        }

        $reader->close();
        $this->removeDirectory($tempDir);

        return $paths;
    }

    private function firstWorksheetPath(ZipArchive $zip): string
    {
        $sheets = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

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

    private function sharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');

        if ($xml === false) {
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

    private function categoryColumnIndex(array $headerMap): ?int
    {
        foreach (['κατηγορια', 'category', 'categories'] as $header) {
            if (array_key_exists($header, $headerMap)) {
                return $headerMap[$header];
            }
        }

        foreach ($headerMap as $header => $index) {
            if (str_contains($header, 'κατηγορια') || str_contains($header, 'category')) {
                return $index;
            }
        }

        return null;
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

    private function normalizeCategoryPath(string $value): string
    {
        $value = str_replace(["\r", "\n", "\t"], ' ', $value);
        $value = preg_replace('/\s+/u', ' ', $value);
        $value = trim($value);
        $value = trim($value, ' >');

        return $value;
    }

    private function splitCategoryPath(string $path): array
    {
        $parts = preg_split('/\s*>\s*/u', $path) ?: [];

        $parts = array_map(function (string $part): string {
            $part = preg_replace('/\s+/u', ' ', $part);

            return trim($part);
        }, $parts);

        return array_values(array_filter($parts, fn (string $part): bool => $part !== ''));
    }

    private function saveCategory(string $name, ?int $parentId, string $fullPath, int $level, int $sortOrder, bool $active): array
    {
        $query = DB::table('categories');

        if ($this->hasColumn('parent_id')) {
            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }

            $query->where('name', $name);
        } elseif ($this->hasColumn('full_path')) {
            $query->where('full_path', $fullPath);
        } else {
            $query->where('name', $name);
        }

        $existing = $query->first();

        $data = [];

        if ($this->hasColumn('name')) {
            $data['name'] = $name;
        }

        if ($this->hasColumn('parent_id')) {
            $data['parent_id'] = $parentId;
        }

        if ($this->hasColumn('slug')) {
            $data['slug'] = $this->uniqueSlug($fullPath, $existing->id ?? null);
        }

        if ($this->hasColumn('full_path')) {
            $data['full_path'] = $fullPath;
        }

        if ($this->hasColumn('level')) {
            $data['level'] = $level;
        }

        if ($this->hasColumn('sort_order') && (! $existing || empty($existing->sort_order))) {
            $data['sort_order'] = $sortOrder;
        }

        if ($this->hasColumn('active')) {
            $data['active'] = $active;
        }

        if ($this->hasColumn('status')) {
            $data['status'] = $active ? 'active' : 'inactive';
        }

        if ($this->hasColumn('filters_enabled')) {
            $data['filters_enabled'] = $active;
        }

        foreach (['xml_feed_enabled', 'xml_only_available', 'boxnow_enabled', 'article_11_enabled'] as $column) {
            if ($this->hasColumn($column) && ! $existing) {
                $data[$column] = false;
            }
        }

        if ($this->hasColumn('updated_at')) {
            $data['updated_at'] = now();
        }

        if ($existing) {
            DB::table('categories')->where('id', $existing->id)->update($data);

            return [
                'id' => (int) $existing->id,
                'created' => false,
            ];
        }

        if ($this->hasColumn('created_at')) {
            $data['created_at'] = now();
        }

        $id = DB::table('categories')->insertGetId($data);

        return [
            'id' => (int) $id,
            'created' => true,
        ];
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

    private function nextSortOrder(?int $parentId): int
    {
        if (! $this->hasColumn('sort_order')) {
            return 10001;
        }

        $query = DB::table('categories');

        if ($this->hasColumn('parent_id')) {
            if ($parentId === null) {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        }

        $max = $query->max('sort_order');

        return $max ? ((int) $max + 2) : 10001;
    }

    private function uniqueSlug(string $fullPath, ?int $ignoreId = null): string
    {
        $base = Str::slug($fullPath);

        if ($base === '') {
            $base = 'category-' . substr(md5($fullPath), 0, 12);
        }

        $slug = $base;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        if (! $this->hasColumn('slug')) {
            return false;
        }

        $query = DB::table('categories')->where('slug', $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function hasColumn(string $column): bool
    {
        return in_array($column, $this->columns, true);
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