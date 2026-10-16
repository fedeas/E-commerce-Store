<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('home_banners')) {
            Schema::create('home_banners', function (Blueprint $table): void {
                $table->id();
                $table->string('title')->nullable();
                $table->string('subtitle')->nullable();
                $table->string('media_type')->default('image')->index();
                $table->string('media_path')->nullable();
                $table->string('media_url')->nullable();
                $table->string('mobile_media_path')->nullable();
                $table->string('link_url')->nullable();
                $table->string('button_label')->nullable();
                $table->boolean('opens_new_tab')->default(false);
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0)->index();
                $table->timestamp('starts_at')->nullable()->index();
                $table->timestamp('ends_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shop_offers')) {
            Schema::create('shop_offers', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('title');
                $table->string('badge')->nullable();
                $table->text('description')->nullable();
                $table->string('image_path')->nullable();
                $table->string('link_url')->nullable();
                $table->decimal('regular_price', 15, 4)->nullable();
                $table->decimal('offer_price', 15, 4)->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0)->index();
                $table->timestamp('starts_at')->nullable()->index();
                $table->timestamp('ends_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('featured_products')) {
            Schema::create('featured_products', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id')->nullable()->index();
                $table->string('source')->default('manual')->index();
                $table->string('label')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->integer('sort_order')->default(0)->index();
                $table->timestamp('starts_at')->nullable()->index();
                $table->timestamp('ends_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_products');
        Schema::dropIfExists('shop_offers');
        Schema::dropIfExists('home_banners');
    }
};
