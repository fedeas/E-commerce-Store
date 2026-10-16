<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->index();
                $table->string('slug')->nullable()->index();
                $table->boolean('active')->default(true)->index();
                $table->timestamps();
            });
        } else {
            Schema::table('brands', function (Blueprint $table): void {
                if (! Schema::hasColumn('brands', 'name')) {
                    $table->string('name')->nullable()->index();
                }

                if (! Schema::hasColumn('brands', 'slug')) {
                    $table->string('slug')->nullable()->index();
                }

                if (! Schema::hasColumn('brands', 'active')) {
                    $table->boolean('active')->default(true)->index();
                }

                if (! Schema::hasColumn('brands', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('brands', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (! Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table): void {
                $table->id();
                $table->string('name')->index();
                $table->string('slug')->nullable()->index();
                $table->string('website_url')->nullable();
                $table->string('api_url')->nullable();
                $table->string('auth_type')->nullable();
                $table->string('username')->nullable();
                $table->text('password')->nullable();
                $table->text('api_token')->nullable();
                $table->boolean('active')->default(true)->index();
                $table->timestamp('last_sync_at')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('suppliers', function (Blueprint $table): void {
                if (! Schema::hasColumn('suppliers', 'name')) {
                    $table->string('name')->nullable()->index();
                }

                if (! Schema::hasColumn('suppliers', 'slug')) {
                    $table->string('slug')->nullable()->index();
                }

                if (! Schema::hasColumn('suppliers', 'website_url')) {
                    $table->string('website_url')->nullable();
                }

                if (! Schema::hasColumn('suppliers', 'api_url')) {
                    $table->string('api_url')->nullable();
                }

                if (! Schema::hasColumn('suppliers', 'auth_type')) {
                    $table->string('auth_type')->nullable();
                }

                if (! Schema::hasColumn('suppliers', 'username')) {
                    $table->string('username')->nullable();
                }

                if (! Schema::hasColumn('suppliers', 'password')) {
                    $table->text('password')->nullable();
                }

                if (! Schema::hasColumn('suppliers', 'api_token')) {
                    $table->text('api_token')->nullable();
                }

                if (! Schema::hasColumn('suppliers', 'active')) {
                    $table->boolean('active')->default(true)->index();
                }

                if (! Schema::hasColumn('suppliers', 'last_sync_at')) {
                    $table->timestamp('last_sync_at')->nullable();
                }

                if (! Schema::hasColumn('suppliers', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('suppliers', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('legacy_id')->nullable()->index();
                $table->string('code')->nullable()->index();
                $table->string('mpn')->nullable()->index();
                $table->string('ean')->nullable()->index();
                $table->text('title')->nullable();
                $table->string('slug')->nullable()->index();
                $table->unsignedBigInteger('brand_id')->nullable()->index();
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->unsignedBigInteger('primary_supplier_id')->nullable()->index();
                $table->string('status')->default('active')->index();
                $table->decimal('vat_rate', 5, 2)->nullable();
                $table->text('short_description')->nullable();
                $table->longText('description')->nullable();
                $table->longText('specs')->nullable();
                $table->decimal('weight_kg', 12, 3)->nullable();
                $table->string('dimensions')->nullable();
                $table->string('main_image')->nullable();
                $table->text('meta_description')->nullable();
                $table->text('meta_keywords')->nullable();
                $table->timestamp('last_imported_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('products', function (Blueprint $table): void {
                if (! Schema::hasColumn('products', 'legacy_id')) {
                    $table->unsignedBigInteger('legacy_id')->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'code')) {
                    $table->string('code')->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'mpn')) {
                    $table->string('mpn')->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'ean')) {
                    $table->string('ean')->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'title')) {
                    $table->text('title')->nullable();
                }

                if (! Schema::hasColumn('products', 'slug')) {
                    $table->string('slug')->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'brand_id')) {
                    $table->unsignedBigInteger('brand_id')->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'category_id')) {
                    $table->unsignedBigInteger('category_id')->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'primary_supplier_id')) {
                    $table->unsignedBigInteger('primary_supplier_id')->nullable()->index();
                }

                if (! Schema::hasColumn('products', 'status') && ! Schema::hasColumn('products', 'active')) {
                    $table->string('status')->default('active')->index();
                }

                if (! Schema::hasColumn('products', 'vat_rate')) {
                    $table->decimal('vat_rate', 5, 2)->nullable();
                }

                if (! Schema::hasColumn('products', 'short_description')) {
                    $table->text('short_description')->nullable();
                }

                if (! Schema::hasColumn('products', 'description')) {
                    $table->longText('description')->nullable();
                }

                if (! Schema::hasColumn('products', 'specs')) {
                    $table->longText('specs')->nullable();
                }

                if (! Schema::hasColumn('products', 'weight_kg')) {
                    $table->decimal('weight_kg', 12, 3)->nullable();
                }

                if (! Schema::hasColumn('products', 'dimensions')) {
                    $table->string('dimensions')->nullable();
                }

                if (! Schema::hasColumn('products', 'main_image')) {
                    $table->string('main_image')->nullable();
                }

                if (! Schema::hasColumn('products', 'meta_description')) {
                    $table->text('meta_description')->nullable();
                }

                if (! Schema::hasColumn('products', 'meta_keywords')) {
                    $table->text('meta_keywords')->nullable();
                }

                if (! Schema::hasColumn('products', 'last_imported_at')) {
                    $table->timestamp('last_imported_at')->nullable();
                }

                if (! Schema::hasColumn('products', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('products', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }

                if (! Schema::hasColumn('products', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (! Schema::hasTable('product_prices')) {
            Schema::create('product_prices', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id')->unique();
                $table->decimal('cost_ex_vat', 15, 4)->nullable();
                $table->decimal('retail_inc_vat', 15, 4)->nullable();
                $table->decimal('discount_inc_vat', 15, 4)->nullable();
                $table->decimal('skroutz_inc_vat', 15, 4)->nullable();
                $table->decimal('wholesale_inc_vat', 15, 4)->nullable();
                $table->decimal('silver_inc_vat', 15, 4)->nullable();
                $table->decimal('gold_inc_vat', 15, 4)->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('product_prices', function (Blueprint $table): void {
                if (! Schema::hasColumn('product_prices', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->nullable()->index();
                }

                foreach ([
                    'cost_ex_vat',
                    'retail_inc_vat',
                    'discount_inc_vat',
                    'skroutz_inc_vat',
                    'wholesale_inc_vat',
                    'silver_inc_vat',
                    'gold_inc_vat',
                ] as $column) {
                    if (! Schema::hasColumn('product_prices', $column)) {
                        $table->decimal($column, 15, 4)->nullable();
                    }
                }

                if (! Schema::hasColumn('product_prices', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn('product_prices', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
    }
};
