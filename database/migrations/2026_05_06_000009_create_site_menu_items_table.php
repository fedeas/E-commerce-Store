<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_menu_items', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable()->index();
            $table->string('position')->default('Πάνω')->index();
            $table->json('sites')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->string('link_url')->nullable();
            $table->timestamps();
        });

        $now = now();
        $rows = [
            ['HOME', 'Πάνω', ['example.com', 'b2b.example.com'], -99, true],
            ['NEW PRODUCTS', 'Πάνω', [], 1, true],
            ['OUR COMPANY', 'Πάνω', ['example.com', 'b2b.example.com'], 1, true],
            ['FLASH SALES', 'Πάνω', [], 20, true],
            ['WHOLESALE', 'Πάνω', ['example.com'], 30, false],
            ['BRANDS', 'Πάνω', [], 35, false],
            ['CONTACT US', 'Πάνω', [], 40, true, 'contact'],
            ['NEWS & ARTICLES', 'Πάνω', [], 90, false],
            ['B2B & WHOLESALE', 'Πάνω', [], 100, false],
            ['Shipping', 'Κάτω Παραγγελία', ['example.com', 'b2b.example.com'], 0, true],
            ['Payment', 'Κάτω Παραγγελία', [], 20, false],
            ['Return policy', 'Κάτω Παραγγελία', [], 30, false],
            ['F.A.Q.', 'Κάτω Πληροφορίες', ['example.com', 'b2b.example.com'], 10, true],
            ['Cookie Policy', 'Κάτω Πληροφορίες', ['example.com', 'b2b.example.com'], 20, true],
            ['Privacy Policy', 'Κάτω Πληροφορίες', ['example.com', 'b2b.example.com'], 25, true],
            ['Terms and Conditions', 'Κάτω Πληροφορίες', ['example.com', 'b2b.example.com'], 30, true, 'terms'],
            ['Αποστολές + πληρωμές προιόντα', 'Διάφορα', [], 0, false],
            ['Εισαγωγή', 'Διάφορα', [], 0, false],
            ['Κείμενο για παραγγελία σε προμηθευτές', 'Διάφορα', [], 0, false],
        ];

        DB::table('site_menu_items')->insert(array_map(fn (array $row): array => [
            'title' => $row[0],
            'position' => $row[1],
            'sites' => json_encode($row[2], JSON_UNESCAPED_UNICODE),
            'sort_order' => $row[3],
            'is_active' => $row[4],
            'slug' => $row[5] ?? Str::slug($row[0]),
            'link_url' => isset($row[5]) ? '/menu/' . $row[5] : null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $rows));
    }

    public function down(): void
    {
        Schema::dropIfExists('site_menu_items');
    }
};
