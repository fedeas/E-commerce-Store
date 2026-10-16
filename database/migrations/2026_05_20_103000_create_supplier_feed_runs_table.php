<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_feed_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 80);
            $table->string('status', 30)->default('queued');
            $table->string('stage')->nullable();
            $table->text('message')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('categories_found')->default(0);
            $table->unsignedInteger('products_created')->default(0);
            $table->unsignedInteger('products_updated')->default(0);
            $table->unsignedInteger('products_skipped')->default(0);
            $table->unsignedInteger('image_candidates')->default(0);
            $table->unsignedInteger('images_saved')->default(0);
            $table->longText('log')->nullable();
            $table->longText('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_feed_runs');
    }
};
