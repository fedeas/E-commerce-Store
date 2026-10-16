<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table): void {
            foreach ([
                'feed_type' => fn () => $table->string('feed_type', 24)->default('xml')->after('api_url'),
                'purchase_markup_percent' => fn () => $table->decimal('purchase_markup_percent', 8, 2)->default(0)->after('feed_type'),
                'sale_markup_percent' => fn () => $table->decimal('sale_markup_percent', 8, 2)->default(0)->after('purchase_markup_percent'),
                'available_text' => fn () => $table->string('available_text')->nullable()->after('sale_markup_percent'),
                'unavailable_text' => fn () => $table->string('unavailable_text')->nullable()->after('available_text'),
                'update_quantities' => fn () => $table->boolean('update_quantities')->default(true)->after('unavailable_text'),
                'update_purchase_price' => fn () => $table->boolean('update_purchase_price')->default(true)->after('update_quantities'),
                'update_sale_price' => fn () => $table->boolean('update_sale_price')->default(false)->after('update_purchase_price'),
                'purchase_price_vat_mode' => fn () => $table->string('purchase_price_vat_mode', 16)->default('ex_vat')->after('update_sale_price'),
                'sale_price_vat_mode' => fn () => $table->string('sale_price_vat_mode', 16)->default('ex_vat')->after('purchase_price_vat_mode'),
                'last_bridge_sync_at' => fn () => $table->timestamp('last_bridge_sync_at')->nullable()->after('last_sync_at'),
            ] as $column => $callback) {
                if (! Schema::hasColumn('suppliers', $column)) {
                    $callback();
                }
            }
        });

        if (! Schema::hasTable('supplier_remote_categories')) {
            Schema::create('supplier_remote_categories', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->string('remote_code');
                $table->string('remote_name');
                $table->unsignedInteger('products_count')->default(0);
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();

                $table->unique(['supplier_id', 'remote_code']);
                $table->index(['supplier_id', 'remote_name']);
            });
        }

        if (! Schema::hasTable('supplier_category_mappings')) {
            Schema::create('supplier_category_mappings', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
                $table->string('remote_code');
                $table->string('remote_name');
                $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['supplier_id', 'remote_code']);
                $table->index(['supplier_id', 'category_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_category_mappings');
        Schema::dropIfExists('supplier_remote_categories');

        Schema::table('suppliers', function (Blueprint $table): void {
            foreach ([
                'feed_type',
                'purchase_markup_percent',
                'sale_markup_percent',
                'available_text',
                'unavailable_text',
                'update_quantities',
                'update_purchase_price',
                'update_sale_price',
                'purchase_price_vat_mode',
                'sale_price_vat_mode',
                'last_bridge_sync_at',
            ] as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
