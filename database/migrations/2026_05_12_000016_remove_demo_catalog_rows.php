<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        $query = DB::table('categories')
            ->whereIn('slug', ['vd-test-do-not-enable-it', 'koybas-dont-enable-it'])
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('products')
                    ->whereColumn('products.category_id', 'categories.id');
            });

        if (Schema::hasColumn('categories', 'active')) {
            $query->where('active', false);
        }

        $query->delete();
    }
};
