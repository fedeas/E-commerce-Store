<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('mpn')->nullable();
            $table->string('ean')->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('primary_supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->decimal('vat_rate', 5, 2)->default(19.00);
            $table->longText('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->json('specs')->nullable();
            $table->decimal('weight_kg', 10, 3)->nullable();
            $table->string('dimensions')->nullable();
            $table->string('main_image')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamp('last_imported_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('mpn');
            $table->index('ean');
            $table->index('title');
            $table->index('status');
            $table->index(['brand_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
