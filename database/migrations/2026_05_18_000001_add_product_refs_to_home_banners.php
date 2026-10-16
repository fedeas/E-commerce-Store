<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('home_banners') && ! Schema::hasColumn('home_banners', 'product_refs')) {
            Schema::table('home_banners', function (Blueprint $table): void {
                $table->text('product_refs')->nullable()->after('media_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('home_banners') && Schema::hasColumn('home_banners', 'product_refs')) {
            Schema::table('home_banners', function (Blueprint $table): void {
                $table->dropColumn('product_refs');
            });
        }
    }
};
