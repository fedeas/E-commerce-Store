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
            if (! Schema::hasColumn('products', 'quantity')) {
                $table->decimal('quantity', 15, 4)->nullable()->default(0);
            }

            if (! Schema::hasColumn('products', 'min_quantity')) {
                $table->decimal('min_quantity', 15, 4)->nullable()->default(1);
            }

            if (! Schema::hasColumn('products', 'set_quantity')) {
                $table->decimal('set_quantity', 15, 4)->nullable()->default(1);
            }

            if (! Schema::hasColumn('products', 'available_from')) {
                $table->date('available_from')->nullable();
            }

            if (! Schema::hasColumn('products', 'stock_text')) {
                $table->string('stock_text')->nullable();
            }

            if (! Schema::hasColumn('products', 'shipping_disabled')) {
                $table->boolean('shipping_disabled')->default(false)->index();
            }

            if (! Schema::hasColumn('products', 'price_update_enabled')) {
                $table->boolean('price_update_enabled')->default(true)->index();
            }

            if (! Schema::hasColumn('products', 'status_update_enabled')) {
                $table->boolean('status_update_enabled')->default(true)->index();
            }

            if (! Schema::hasColumn('products', 'purchase_price_update_enabled')) {
                $table->boolean('purchase_price_update_enabled')->default(true)->index();
            }

            if (! Schema::hasColumn('products', 'addon_price_fixed')) {
                $table->decimal('addon_price_fixed', 15, 4)->nullable()->default(0);
            }

            if (! Schema::hasColumn('products', 'addon_price_percent')) {
                $table->decimal('addon_price_percent', 15, 4)->nullable()->default(0);
            }

            if (! Schema::hasColumn('products', 'minimum_price_inc_vat')) {
                $table->decimal('minimum_price_inc_vat', 15, 4)->nullable()->default(0);
            }

            if (! Schema::hasColumn('products', 'youtube_url')) {
                $table->string('youtube_url')->nullable();
            }

            if (! Schema::hasColumn('products', 'image_path')) {
                $table->string('image_path')->nullable();
            }
        });
    }

    public function down(): void
    {
    }
};
