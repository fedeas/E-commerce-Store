<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('home_banners')) {
            Schema::table('home_banners', function (Blueprint $table): void {
                if (! Schema::hasColumn('home_banners', 'placement')) {
                    $table->string('placement')->default('top')->index()->after('id');
                }
            });
        }

        if (Schema::hasTable('shop_offers')) {
            Schema::table('shop_offers', function (Blueprint $table): void {
                if (! Schema::hasColumn('shop_offers', 'discount_percent')) {
                    $table->decimal('discount_percent', 8, 2)->nullable()->after('offer_price');
                }
            });
        }

        if (! Schema::hasTable('home_product_sections')) {
            Schema::create('home_product_sections', function (Blueprint $table): void {
                $table->id();
                $table->string('section_key')->unique();
                $table->string('title')->nullable();
                $table->string('item_type')->default('recommended')->index();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0)->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_product_sections');
    }
};
