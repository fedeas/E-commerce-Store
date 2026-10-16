<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table): void {
            if (! Schema::hasColumn('products', 'boxnow_enabled')) {
                $table->boolean('boxnow_enabled')->default(false)->index();
            }

            if (! Schema::hasColumn('products', 'article_11_enabled')) {
                $table->boolean('article_11_enabled')->default(false)->index();
            }
        });
    }

    public function down(): void
    {
    }
};
