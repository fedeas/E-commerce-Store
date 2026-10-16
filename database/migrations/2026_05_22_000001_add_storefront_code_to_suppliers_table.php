<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('suppliers')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table): void {
            if (! Schema::hasColumn('suppliers', 'storefront_code')) {
                $table->string('storefront_code', 12)->nullable()->after('slug')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('suppliers') || ! Schema::hasColumn('suppliers', 'storefront_code')) {
            return;
        }

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropColumn('storefront_code');
        });
    }
};
