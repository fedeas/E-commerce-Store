<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        if (Schema::hasColumn('suppliers', 'api_url')) {
            Schema::table('suppliers', function (Blueprint $table): void {
                $table->text('api_url')->nullable()->change();
            });
        }

        if (Schema::hasColumn('suppliers', 'product_data_url')) {
            Schema::table('suppliers', function (Blueprint $table): void {
                $table->text('product_data_url')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        if (Schema::hasColumn('suppliers', 'api_url')) {
            Schema::table('suppliers', function (Blueprint $table): void {
                $table->string('api_url')->nullable()->change();
            });
        }

        if (Schema::hasColumn('suppliers', 'product_data_url')) {
            Schema::table('suppliers', function (Blueprint $table): void {
                $table->string('product_data_url')->nullable()->change();
            });
        }
    }
};
