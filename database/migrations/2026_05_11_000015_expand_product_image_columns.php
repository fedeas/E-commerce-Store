<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'image_path')) {
                Schema::table('products', function (Blueprint $table): void {
                    $table->text('image_path')->nullable()->change();
                });
            }

            if (Schema::hasColumn('products', 'main_image')) {
                Schema::table('products', function (Blueprint $table): void {
                    $table->text('main_image')->nullable()->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            if (Schema::hasColumn('products', 'image_path')) {
                Schema::table('products', function (Blueprint $table): void {
                    $table->string('image_path')->nullable()->change();
                });
            }

            if (Schema::hasColumn('products', 'main_image')) {
                Schema::table('products', function (Blueprint $table): void {
                    $table->string('main_image')->nullable()->change();
                });
            }
        }
    }
};
