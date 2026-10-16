<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'active') && ! Schema::hasColumn('categories', 'status')) {
                $table->boolean('active')->default(true);
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
        });

        if (Schema::hasColumn('categories', 'filters_enabled')) {
            if (Schema::hasColumn('categories', 'active')) {
                DB::table('categories')
                    ->where('active', true)
                    ->update(['filters_enabled' => true]);
            } elseif (Schema::hasColumn('categories', 'status')) {
                DB::table('categories')
                    ->where('status', 'active')
                    ->update(['filters_enabled' => true]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table): void {
            foreach ([
                'xml_feed_enabled',
                'xml_only_available',
                'filters_enabled',
                'boxnow_enabled',
                'article_11_enabled',
            ] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
