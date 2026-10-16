<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('id')->index();
            }

            if (! Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->nullable()->index();
            }

            if (! Schema::hasColumn('categories', 'full_path')) {
                $table->string('full_path')->nullable()->index();
            }

            if (! Schema::hasColumn('categories', 'level')) {
                $table->unsignedInteger('level')->default(0)->index();
            }

            if (! Schema::hasColumn('categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->index();
            }

            if (! Schema::hasColumn('categories', 'active') && ! Schema::hasColumn('categories', 'status')) {
                $table->boolean('active')->default(true)->index();
            }

            if (! Schema::hasColumn('categories', 'xml_feed_enabled')) {
                $table->boolean('xml_feed_enabled')->default(false);
            }

            if (! Schema::hasColumn('categories', 'xml_only_available')) {
                $table->boolean('xml_only_available')->default(false);
            }

            if (! Schema::hasColumn('categories', 'filters_enabled')) {
                $table->boolean('filters_enabled')->default(false);
            }

            if (! Schema::hasColumn('categories', 'boxnow_enabled')) {
                $table->boolean('boxnow_enabled')->default(false);
            }

            if (! Schema::hasColumn('categories', 'article_11_enabled')) {
                $table->boolean('article_11_enabled')->default(false);
            }

            if (! Schema::hasColumn('categories', 'category_image')) {
                $table->string('category_image')->nullable();
            }

            if (! Schema::hasColumn('categories', 'feed_fixed_markup')) {
                $table->decimal('feed_fixed_markup', 10, 2)->default(0);
            }

            if (! Schema::hasColumn('categories', 'feed_percent_markup')) {
                $table->decimal('feed_percent_markup', 10, 2)->default(0);
            }

            if (! Schema::hasColumn('categories', 'product_weight_multiplier')) {
                $table->decimal('product_weight_multiplier', 10, 3)->default(0);
            }
        });
    }

    public function down(): void
    {
    }
};
