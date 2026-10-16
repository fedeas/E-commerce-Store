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
        Schema::create('product_extra_field_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->nullable()->index();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('product_extra_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('section_id')->nullable()->constrained('product_extra_field_sections')->nullOnDelete();
            $table->string('name')->index();
            $table->string('internal_code')->nullable()->index();
            $table->text('tags')->nullable();
            $table->string('field_type')->default('text')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_filterable')->default(true)->index();
            $table->boolean('is_multiple')->default(false)->index();
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('product_extra_field_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('extra_field_id')->constrained('product_extra_fields')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->timestamps();

            $table->index(['extra_field_id', 'product_id']);
        });

        $now = now();
        $sectionId = DB::table('product_extra_field_sections')->insertGetId([
            'name' => 'TECHNICAL DETAILS',
            'slug' => 'technical-details',
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $fields = [
            'Accessory Type',
            'All in one',
            'Brand',
            'Case Type',
            'Connection Type',
            'CPU',
            'CPU Brand',
            'Data Cable Type',
            'Definition',
            'Disk Size',
            'Disk Type',
            'Gaming',
            'GPU',
            'GPU Brand',
            'OS System',
            'RAM',
            'RAM Type',
            'Refresh Rate',
            'Response Time',
            'Scanner Accessory Type',
            'Scanner Type',
            'Screen Size',
            'VRAM',
            'VRAM Type',
            'Wattage',
            'AV Type',
            'Cat Type',
            'Compatibility',
            'OM Type',
            'Fiber Mode',
            'Cable size',
            'Resolution',
            'Interface / Connector',
            'USB Version',
            'HDMI Version',
            'DisplayPort Version',
            'Cable Length',
            'Cable Color',
            'PoE',
            'Number of Ports',
            'Switch Speed',
            'Wi-Fi Standard',
            'Bluetooth',
            'Printer Type',
            'Printer Color / Mono',
            'Paper Size',
            'Cartridge / Toner Type',
            'Form Factor',
            'Socket',
            'Chipset',
            'PSU Efficiency',
            'Fan Size',
            'VESA',
            'Curved / Flat',
            'Panel Type',
            'Brightness',
            'Webcam Resolution',
            'Microphone / Speaker Included',
        ];

        DB::table('product_extra_fields')->insert(array_map(
            fn (string $field, int $index): array => [
                'section_id' => $sectionId,
                'name' => $field,
                'internal_code' => Str::slug($field, '_'),
                'tags' => $this->defaultTags($field),
                'field_type' => 'text',
                'is_active' => ! in_array($field, ['Brand'], true),
                'is_filterable' => ! in_array($field, ['Brand'], true),
                'is_multiple' => in_array($field, ['Compatibility', 'Interface / Connector'], true),
                'sort_order' => ($index + 1) * 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            $fields,
            array_keys($fields),
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('product_extra_field_values');
        Schema::dropIfExists('product_extra_fields');
        Schema::dropIfExists('product_extra_field_sections');
    }

    private function defaultTags(string $field): ?string
    {
        return match ($field) {
            'Screen Size' => 'Display Size, Monitor Size, Inch, Inches',
            'Resolution' => 'Definition, Display Resolution, Full HD, 4K, QHD',
            'Cable Length' => 'Cable size, Length',
            'Interface / Connector' => 'Connection Type, Connector, Port',
            'Number of Ports' => 'Ports, Port Count',
            'Wi-Fi Standard' => 'WiFi, Wireless',
            'Printer Color / Mono' => 'Color, Colour, Mono, Monochrome',
            'Brand' => 'Manufacturer, Κατασκευαστής',
            default => null,
        };
    }
};
