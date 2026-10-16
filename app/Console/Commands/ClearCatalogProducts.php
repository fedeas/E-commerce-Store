<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearCatalogProducts extends Command
{
    protected $signature = 'catalog:clear-products {--force}';

    protected $description = 'Delete catalog products and dependent product data while keeping categories, suppliers and bridge mappings.';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('This command is destructive. Re-run with --force.');

            return self::FAILURE;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ([
            'product_extra_field_values',
            'product_images',
            'product_prices',
            'supplier_products',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
                $this->info('Cleared: ' . $table);
            }
        }

        if (Schema::hasTable('product_reviews') && Schema::hasColumn('product_reviews', 'product_id')) {
            DB::table('product_reviews')->update(['product_id' => null]);
        }

        foreach (['shop_offers', 'featured_products'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'product_id')) {
                DB::table($table)->update(['product_id' => null]);
            }
        }

        if (Schema::hasTable('products')) {
            DB::table('products')->truncate();
            $this->info('Cleared: products');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Catalog products cleared. Categories, suppliers and bridge mappings were kept.');

        return self::SUCCESS;
    }
}
