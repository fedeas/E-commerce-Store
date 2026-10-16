<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        if (Schema::hasColumn('suppliers', 'api_url')) {
            DB::statement('ALTER TABLE suppliers MODIFY api_url TEXT NULL');
        }

        if (Schema::hasColumn('suppliers', 'product_data_url')) {
            DB::statement('ALTER TABLE suppliers MODIFY product_data_url TEXT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        if (Schema::hasColumn('suppliers', 'api_url')) {
            DB::statement('ALTER TABLE suppliers MODIFY api_url VARCHAR(255) NULL');
        }

        if (Schema::hasColumn('suppliers', 'product_data_url')) {
            DB::statement('ALTER TABLE suppliers MODIFY product_data_url VARCHAR(255) NULL');
        }
    }
};
