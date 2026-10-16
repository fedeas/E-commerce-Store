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
                $table->string('name')->unique();
                $table->string('slug')->nullable()->index();
                $table->timestamps();
            });
        }

        Schema::table('brands', function (Blueprint $table): void {
            if (! Schema::hasColumn('brands', 'active')) {
                $table->boolean('active')->default(true)->after('slug');
            }

            if (! Schema::hasColumn('brands', 'website_url')) {
                $table->string('website_url')->nullable()->after('active');
            }

            if (! Schema::hasColumn('brands', 'image_path')) {
                $table->string('image_path')->nullable()->after('website_url');
            }

            if (! Schema::hasColumn('brands', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('image_path');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('brands')) {
            return;
        }

        Schema::table('brands', function (Blueprint $table): void {
            foreach (['active', 'website_url', 'image_path', 'sort_order'] as $column) {
                if (Schema::hasColumn('brands', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
