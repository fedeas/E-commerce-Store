<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('matched_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('supplier_sku')->nullable();
            $table->string('mpn')->nullable();
            $table->string('ean')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('title');
            $table->string('category_raw')->nullable();
            $table->decimal('cost_ex_vat', 12, 4)->nullable();
            $table->decimal('retail_inc_vat', 12, 4)->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->string('availability')->nullable();
            $table->json('images')->nullable();
            $table->json('raw_data')->nullable();
            $table->string('match_status')->default('unmatched');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('supplier_id');
            $table->index('matched_product_id');
            $table->index('supplier_sku');
            $table->index('mpn');
            $table->index('ean');
            $table->index('match_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_products');
    }
};
