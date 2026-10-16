<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NormalizeBrands extends Command
{
    protected $signature = 'catalog:normalize-brands {--apply}';

    protected $description = 'Detect and merge duplicate manufacturer/brand names.';

    public function handle(): int
    {
        if (! Schema::hasTable('brands')) {
            $this->error('Table brands does not exist.');
            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');

        $brands = DB::table('brands')
            ->orderBy('name')
            ->get();

        if ($brands->isEmpty()) {
            $this->info('No brands found.');
            return self::SUCCESS;
        }

        $plans = $this->buildMergePlans($brands);

        if ($plans === []) {
            $this->info('No duplicate/alias brands found.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info($apply ? 'APPLY MODE - the following brands will be merged:' : 'DRY RUN - the following brands would be merged:');
        $this->newLine();

        foreach ($plans as $plan) {
            $this->line('Target: [' . $plan['target_id'] . '] ' . $plan['target_name'] . ' => final name: ' . $plan['canonical_name']);

            foreach ($plan['duplicates'] as $duplicate) {
                $this->line('  - merge [' . $duplicate['id'] . '] ' . $duplicate['name'] . ' | products: ' . $duplicate['products_count']);
            }

            $this->newLine();
        }

        if (! $apply) {
            $this->warn('Nothing changed. Run with --apply to merge.');
            return self::SUCCESS;
        }

        $this->backupBeforeMerge();

        DB::transaction(function () use ($plans): void {
            foreach ($plans as $plan) {
                $targetId = (int) $plan['target_id'];
                $targetName = (string) $plan['canonical_name'];
                $duplicateIds = collect($plan['duplicates'])->pluck('id')->map(fn ($id): int => (int) $id)->all();
                $duplicateNames = collect($plan['duplicates'])->pluck('name')->map(fn ($name): string => (string) $name)->all();

                if ($duplicateIds === []) {
                    continue;
                }

                if (Schema::hasTable('products') && Schema::hasColumn('products', 'brand_id')) {
                    DB::table('products')
                        ->whereIn('brand_id', $duplicateIds)
                        ->update(['brand_id' => $targetId]);
                }

                if (Schema::hasTable('products') && Schema::hasColumn('products', 'manufacturer')) {
                    DB::table('products')
                        ->whereIn('manufacturer', $duplicateNames)
                        ->update(['manufacturer' => $targetName]);
                }

                if (Schema::hasTable('products') && Schema::hasColumn('products', 'brand')) {
                    DB::table('products')
                        ->whereIn('brand', $duplicateNames)
                        ->update(['brand' => $targetName]);
                }

                $brandUpdate = [
                    'name' => $targetName,
                ];

                if (Schema::hasColumn('brands', 'slug')) {
                    $brandUpdate['slug'] = Str::slug($targetName);
                }

                if (Schema::hasColumn('brands', 'updated_at')) {
                    $brandUpdate['updated_at'] = now();
                }

                DB::table('brands')
                    ->where('id', $targetId)
                    ->update($brandUpdate);

                DB::table('brands')
                    ->whereIn('id', $duplicateIds)
                    ->delete();
            }
        });

        $this->info('Brand normalization completed.');

        return self::SUCCESS;
    }

    private function buildMergePlans($brands): array
    {
        $plans = [];
        $usedDuplicateIds = [];

        foreach ($this->aliasMap() as $canonicalName => $aliases) {
            $matched = $brands->filter(function ($brand) use ($canonicalName, $aliases): bool {
                $brandName = $this->cleanName((string) $brand->name);
                $brandSlug = property_exists($brand, 'slug') ? $this->cleanName((string) $brand->slug) : '';

                if ($brandName === $this->cleanName($canonicalName) || $brandSlug === $this->cleanName($canonicalName)) {
                    return true;
                }

                foreach ($aliases as $alias) {
                    $aliasKey = $this->cleanName($alias);

                    if ($brandName === $aliasKey || $brandSlug === $aliasKey) {
                        return true;
                    }
                }

                return false;
            })->values();

            if ($matched->count() <= 1) {
                continue;
            }

            $target = $this->chooseTargetBrand($matched, $canonicalName);

            $duplicates = $matched
                ->filter(fn ($brand): bool => (int) $brand->id !== (int) $target->id)
                ->map(function ($brand): array {
                    return [
                        'id' => (int) $brand->id,
                        'name' => (string) $brand->name,
                        'products_count' => $this->countProductsForBrand((int) $brand->id, (string) $brand->name),
                    ];
                })
                ->values()
                ->all();

            if ($duplicates === []) {
                continue;
            }

            foreach ($duplicates as $duplicate) {
                $usedDuplicateIds[] = (int) $duplicate['id'];
            }

            $plans[] = [
                'canonical_name' => $canonicalName,
                'target_id' => (int) $target->id,
                'target_name' => (string) $target->name,
                'duplicates' => $duplicates,
            ];
        }

        $groups = $brands
            ->reject(fn ($brand): bool => in_array((int) $brand->id, $usedDuplicateIds, true))
            ->groupBy(fn ($brand): string => $this->normalKey((string) $brand->name))
            ->filter(fn ($group, string $key): bool => $key !== '' && $group->count() > 1);

        foreach ($groups as $key => $group) {
            $target = $group->sortBy(fn ($brand): int => strlen((string) $brand->name))->first();

            if (! $target) {
                continue;
            }

            $duplicates = $group
                ->filter(fn ($brand): bool => (int) $brand->id !== (int) $target->id)
                ->map(function ($brand): array {
                    return [
                        'id' => (int) $brand->id,
                        'name' => (string) $brand->name,
                        'products_count' => $this->countProductsForBrand((int) $brand->id, (string) $brand->name),
                    ];
                })
                ->values()
                ->all();

            if ($duplicates === []) {
                continue;
            }

            $plans[] = [
                'canonical_name' => (string) $target->name,
                'target_id' => (int) $target->id,
                'target_name' => (string) $target->name,
                'duplicates' => $duplicates,
            ];
        }

        return $plans;
    }

    private function aliasMap(): array
    {
        return [
            'Acer' => ['Acer Inc', 'Acer Computer', 'Acer Europe'],
            'ADATA' => ['A-DATA', 'Adata Technology', 'ADATA Technology'],
            'AOC' => ['AOC International', 'AOC Monitors'],
            'Apple' => ['Apple Inc', 'Apple Computer'],
            'APC' => ['APC by Schneider Electric', 'American Power Conversion'],
            'ASUS' => ['ASUSTeK Computer Inc', 'ASUSTeK', 'ASUS COMPUTER', 'Asus Computer International'],
            'BenQ' => ['BenQ Corporation', 'Benq'],
            'BROTHER' => ['Brother Industries', 'Brother International', 'Brother International GmbH'],
            'CANON' => ['Canon Deutschland GmbH', 'Canon Europe', 'Canon Inc', 'Canon Europa'],
            'Cooler Master' => ['Cooler Master Technology', 'Coolermaster'],
            'Corsair' => ['Corsair Memory', 'Corsair Components'],
            'DAHUA' => ['Dahua', 'Dahua Technology', 'Dahua-TV', 'Dahua-Wireless', 'Dahua Wireless', 'Dahua Technology Co'],
            'Dell' => ['Dell Technologies', 'Dell GmbH', 'Dell Inc', 'Dell Computer'],
            'Eaton' => ['Eaton Electric', 'Eaton Industries'],
            'Epson' => ['Epson Europe', 'Seiko Epson', 'Epson Deutschland GmbH'],
            'GIGABYTE' => ['Gigabyte Technology', 'Giga-Byte Technology'],
            'HP' => ['HP Inc', 'Hewlett Packard', 'Hewlett-Packard', 'HP Deutschland GmbH'],
            'Intel' => ['Intel Corporation', 'Intel Corp'],
            'Kingston' => ['Kingston Technology', 'Kingston Technology Company'],
            'Lenovo' => ['Lenovo Deutschland GmbH', 'Lenovo Group', 'Lenovo PC'],
            'LG' => ['LG Electronics', 'LG Electronics Inc'],
            'Logitech' => ['Logitech Europe', 'Logitech Europe S.A.', 'Logitech International'],
            'Microsoft' => ['Microsoft Corporation', 'Microsoft Corp'],
            'MSI' => ['MSI COMPUTER', 'Micro-Star International', 'Micro Star International'],
            'NVIDIA' => ['Nvidia Corporation', 'Nvidia'],
            'Samsung' => ['Samsung Electronics', 'Samsung Electronics Co', 'Samsung Electronics GmbH'],
            'Seagate' => ['Seagate Technology'],
            'Sony' => ['Sony Corporation', 'Sony Europe'],
            'TP-LINK' => ['TP Link', 'TPLink', 'TP-Link Technologies', 'TP-Link Deutschland'],
            'Ubiquiti' => ['Ubiquiti Networks', 'Ubiquiti Inc'],
            'Western Digital' => ['WD', 'Western Digital Technologies'],
            'Xerox' => ['Xerox Corporation'],
            'Xiaomi' => ['Xiaomi Corporation', 'Xiaomi Technology'],
        ];
    }

    private function chooseTargetBrand($matched, string $canonicalName): object
    {
        $exact = $matched->first(fn ($brand): bool => $this->cleanName((string) $brand->name) === $this->cleanName($canonicalName));

        if ($exact) {
            return $exact;
        }

        return $matched->sortBy(fn ($brand): int => strlen((string) $brand->name))->first();
    }

    private function normalKey(string $name): string
    {
        $name = $this->cleanName($name);

        $remove = [
            'deutschland',
            'germany',
            'europe',
            'europa',
            'gmbh',
            'inc',
            'incorporated',
            'corporation',
            'corp',
            'limited',
            'ltd',
            'company',
            'co',
            'bv',
            'nv',
            'ag',
            'sa',
            's.a',
            'srl',
            's.r.l',
            'llc',
            'plc',
        ];

        foreach ($remove as $word) {
            $name = preg_replace('/\b' . preg_quote($word, '/') . '\b/u', ' ', $name);
        }

        $name = preg_replace('/\s+/u', ' ', $name);

        return trim($name);
    }

    private function cleanName(string $name): string
    {
        $name = Str::ascii($name);
        $name = strtolower($name);
        $name = str_replace(['###', '##', '#'], '', $name);
        $name = str_replace(['&'], ' and ', $name);
        $name = preg_replace('/[^a-z0-9]+/u', ' ', $name);
        $name = preg_replace('/\s+/u', ' ', $name);

        return trim($name);
    }

    private function countProductsForBrand(int $brandId, string $brandName): int
    {
        if (! Schema::hasTable('products')) {
            return 0;
        }

        if (Schema::hasColumn('products', 'brand_id')) {
            return (int) DB::table('products')
                ->where('brand_id', $brandId)
                ->count();
        }

        if (Schema::hasColumn('products', 'manufacturer')) {
            return (int) DB::table('products')
                ->where('manufacturer', $brandName)
                ->count();
        }

        if (Schema::hasColumn('products', 'brand')) {
            return (int) DB::table('products')
                ->where('brand', $brandName)
                ->count();
        }

        return 0;
    }

    private function backupBeforeMerge(): void
    {
        $directory = storage_path('app/backups/brand-normalization');

        if (! is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $stamp = now()->format('Ymd_His');

        $brandsFile = $directory . '/brands_before_' . $stamp . '.csv';
        $brandsHandle = fopen($brandsFile, 'w');

        fputcsv($brandsHandle, ['id', 'name', 'slug', 'active', 'website_url', 'image_path', 'sort_order', 'created_at', 'updated_at']);

        DB::table('brands')->orderBy('id')->get()->each(function ($brand) use ($brandsHandle): void {
            fputcsv($brandsHandle, [
                $brand->id ?? '',
                $brand->name ?? '',
                $brand->slug ?? '',
                $brand->active ?? '',
                $brand->website_url ?? '',
                $brand->image_path ?? '',
                $brand->sort_order ?? '',
                $brand->created_at ?? '',
                $brand->updated_at ?? '',
            ]);
        });

        fclose($brandsHandle);

        if (Schema::hasTable('products')) {
            $productsFile = $directory . '/products_brand_before_' . $stamp . '.csv';
            $productsHandle = fopen($productsFile, 'w');

            $columns = ['id'];

            foreach (['brand_id', 'manufacturer', 'brand'] as $column) {
                if (Schema::hasColumn('products', $column)) {
                    $columns[] = $column;
                }
            }

            fputcsv($productsHandle, $columns);

            DB::table('products')
                ->select($columns)
                ->orderBy('id')
                ->chunk(1000, function ($products) use ($productsHandle, $columns): void {
                    foreach ($products as $product) {
                        $row = [];

                        foreach ($columns as $column) {
                            $row[] = $product->{$column} ?? '';
                        }

                        fputcsv($productsHandle, $row);
                    }
                });

            fclose($productsHandle);
        }

        $this->info('Backup created in: ' . $directory);
    }
}
