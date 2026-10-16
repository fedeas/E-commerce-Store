<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stock_statuses')) {
            Schema::create('stock_statuses', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('skroutz_xml_name')->nullable();
                $table->boolean('is_available')->default(false);
                $table->boolean('is_purchasable')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'stock_status_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->foreignId('stock_status_id')->nullable()->after('stock_text')->constrained('stock_statuses')->nullOnDelete();
            });
        }

        $now = now();

        $rows = [
            ['name' => '8-12 business days', 'skroutz_xml_name' => '8-12 days', 'is_available' => true, 'is_purchasable' => true, 'sort_order' => 1],
            ['name' => 'Available', 'skroutz_xml_name' => 'Available', 'is_available' => true, 'is_purchasable' => true, 'sort_order' => 2],
            ['name' => 'Available from 1 to 3 days', 'skroutz_xml_name' => 'Available from 1 to 3 days', 'is_available' => true, 'is_purchasable' => true, 'sort_order' => 3],
            ['name' => 'Available from 4 to 10 days', 'skroutz_xml_name' => 'Available from 4 to 10 days', 'is_available' => true, 'is_purchasable' => true, 'sort_order' => 4],
            ['name' => 'Available up to 30 days', 'skroutz_xml_name' => 'Available up to 30 days', 'is_available' => false, 'is_purchasable' => true, 'sort_order' => 5],
            ['name' => 'Coming soon', 'skroutz_xml_name' => 'Coming soon', 'is_available' => false, 'is_purchasable' => true, 'sort_order' => 6],
            ['name' => 'Contact us', 'skroutz_xml_name' => 'Contact us', 'is_available' => false, 'is_purchasable' => true, 'sort_order' => 7],
            ['name' => 'Preorder', 'skroutz_xml_name' => 'Preorder', 'is_available' => false, 'is_purchasable' => true, 'sort_order' => 8],
            ['name' => 'Upon order', 'skroutz_xml_name' => 'Upon order', 'is_available' => false, 'is_purchasable' => true, 'sort_order' => 9],
        ];

        foreach ($rows as $row) {
            DB::table('stock_statuses')->updateOrInsert(
                ['name' => $row['name']],
                [
                    'skroutz_xml_name' => $row['skroutz_xml_name'],
                    'is_available' => $row['is_available'],
                    'is_purchasable' => $row['is_purchasable'],
                    'sort_order' => $row['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'stock_status_id')) {
            Schema::table('products', function (Blueprint $table): void {
                $table->dropForeign(['stock_status_id']);
                $table->dropColumn('stock_status_id');
            });
        }

        Schema::dropIfExists('stock_statuses');
    }
};
