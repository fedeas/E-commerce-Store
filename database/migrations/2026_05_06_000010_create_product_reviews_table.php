<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name')->nullable();
            $table->unsignedTinyInteger('rating')->default(5)->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->longText('review_text')->nullable();
            $table->longText('reply_text')->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->boolean('is_answered')->default(false)->index();
            $table->timestamp('added_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};
