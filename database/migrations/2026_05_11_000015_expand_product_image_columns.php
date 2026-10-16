<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'image_path')) {
                DB::statement('ALTER TABLE products MODIFY image_path TEXT NULL');
            }

            if (Schema::hasColumn('products', 'main_image')) {
                DB::statement('ALTER TABLE products MODIFY main_image TEXT NULL');
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'image_path')) {
                DB::statement('ALTER TABLE products MODIFY image_path VARCHAR(255) NULL');
            }

            if (Schema::hasColumn('products', 'main_image')) {
                DB::statement('ALTER TABLE products MODIFY main_image VARCHAR(255) NULL');
            }
        }
    }
};
