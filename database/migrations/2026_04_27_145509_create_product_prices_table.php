<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('cost_ex_vat', 12, 4)->nullable();
            $table->decimal('retail_inc_vat', 12, 4)->nullable();
            $table->decimal('discount_inc_vat', 12, 4)->nullable();
            $table->decimal('skroutz_inc_vat', 12, 4)->nullable();
            $table->decimal('wholesale_inc_vat', 12, 4)->nullable();
            $table->decimal('silver_inc_vat', 12, 4)->nullable();
            $table->decimal('gold_inc_vat', 12, 4)->nullable();
            $table->timestamps();

            $table->unique('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
