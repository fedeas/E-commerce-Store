<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RestoreBrandBackup extends Command
{
    protected $signature = 'catalog:restore-brand-backup {timestamp}';

    protected $description = 'Restore brands and product brand links from brand-normalization backup CSV files.';

    public function handle(): int
    {
        $timestamp = (string) $this->argument('timestamp');

        $directory = storage_path('app/backups/brand-normalization');

        $brandsFile = $directory . '/brands_before_' . $timestamp . '.csv';
        $productsFile = $directory . '/products_brand_before_' . $timestamp . '.csv';

        if (! file_exists($brandsFile)) {
            $this->error('Brands backup not found: ' . $brandsFile);
            return self::FAILURE;
        }

        if (! file_exists($productsFile)) {
            $this->error('Products backup not found: ' . $productsFile);
            return self::FAILURE;
        }

        if (! Schema::hasTable('brands')) {
            $this->error('Table brands does not exist.');
            return self::FAILURE;
        }

        if (! Schema::hasTable('products')) {
            $this->error('Table products does not exist.');
            return self::FAILURE;
        }

        $this->info('Restoring brands from: ' . $brandsFile);
        $this->info('Restoring product brand links from: ' . $productsFile);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::transaction(function () use ($brandsFile, $productsFile): void {
                DB::table('brands')->truncate();

                $brandsHandle = fopen($brandsFile, 'r');

                if ($brandsHandle === false) {
                    throw new \RuntimeException('Could not open brands backup.');
                }

                $brandHeaders = fgetcsv($brandsHandle);

                if (! is_array($brandHeaders)) {
                    throw new \RuntimeException('Invalid brands backup headers.');
                }

                $brandRows = [];
                $brandCount = 0;

                while (($row = fgetcsv($brandsHandle)) !== false) {
                    $data = $this->combineRow($brandHeaders, $row);

                    $insert = [];

                    foreach ($data as $column => $value) {
                        if (! Schema::hasColumn('brands', $column)) {
                            continue;
                        }

                        $insert[$column] = $this->normalizeValue($value);
                    }

                    if (! isset($insert['id']) || ! isset($insert['name'])) {
                        continue;
                    }

                    $brandRows[] = $insert;
                    $brandCount++;

                    if (count($brandRows) >= 500) {
                        DB::table('brands')->insert($brandRows);
                        $brandRows = [];
                    }
                }

                if ($brandRows !== []) {
                    DB::table('brands')->insert($brandRows);
                }

                fclose($brandsHandle);

                $productsHandle = fopen($productsFile, 'r');

                if ($productsHandle === false) {
                    throw new \RuntimeException('Could not open products backup.');
                }

                $productHeaders = fgetcsv($productsHandle);

                if (! is_array($productHeaders)) {
                    throw new \RuntimeException('Invalid products backup headers.');
                }

                $productCount = 0;

                while (($row = fgetcsv($productsHandle)) !== false) {
                    $data = $this->combineRow($productHeaders, $row);

                    if (! isset($data['id']) || trim((string) $data['id']) === '') {
                        continue;
                    }

                    $updates = [];

                    foreach (['brand_id', 'manufacturer', 'brand'] as $column) {
                        if (! array_key_exists($column, $data)) {
                            continue;
                        }

                        if (! Schema::hasColumn('products', $column)) {
                            continue;
                        }

                        $updates[$column] = $this->normalizeValue($data[$column]);
                    }

                    if ($updates === []) {
                        continue;
                    }

                    DB::table('products')
                        ->where('id', (int) $data['id'])
                        ->update($updates);

                    $productCount++;
                }

                fclose($productsHandle);

                $this->info('Brands restored: ' . $brandCount);
                $this->info('Products restored: ' . $productCount);
            });
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('Brand backup restore completed.');

        return self::SUCCESS;
    }

    private function combineRow(array $headers, array $row): array
    {
        $data = [];

        foreach ($headers as $index => $header) {
            $data[(string) $header] = $row[$index] ?? null;
        }

        return $data;
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === '') {
            return null;
        }

        return $value;
    }
}
