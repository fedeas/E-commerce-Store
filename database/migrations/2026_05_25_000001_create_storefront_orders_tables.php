<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('storefront_orders')) {
            Schema::create('storefront_orders', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('order_number')->unique();
                $table->string('status', 40)->default('new')->index();
                $table->string('locale', 5)->default('en');
                $table->string('customer_type', 20)->default('retail')->index();
                $table->string('customer_name');
                $table->string('customer_email');
                $table->string('customer_phone')->nullable();
                $table->string('company_name')->nullable();
                $table->string('vat_number')->nullable();
                $table->string('fulfillment_method', 30)->default('pickup')->index();
                $table->string('pickup_store', 40)->nullable();
                $table->string('delivery_address')->nullable();
                $table->string('city')->nullable();
                $table->string('postcode', 30)->nullable();
                $table->text('notes')->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->string('price_note')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('storefront_order_items')) {
            Schema::create('storefront_order_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('storefront_order_id')->constrained('storefront_orders')->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->text('product_title');
                $table->string('product_code')->nullable();
                $table->string('display_code')->nullable();
                $table->string('brand')->nullable();
                $table->string('mpn')->nullable();
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('line_total', 12, 2)->default(0);
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_order_items');
        Schema::dropIfExists('storefront_orders');
    }
};
