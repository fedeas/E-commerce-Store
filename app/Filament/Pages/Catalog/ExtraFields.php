<?php

namespace App\Filament\Pages\Catalog;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class ExtraFields extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';

    protected static ?string $navigationLabel = 'Έξτρα πεδία';

    protected static ?string $title = 'Έξτρα πεδία';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?int $navigationSort = 26;

    protected string $view = 'filament.pages.catalog.extra-fields';

    public string $search = '';

    public string $sectionFilter = 'all';

    public ?int $selectedFieldId = null;

    protected array $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'sectionFilter' => ['except' => 'all', 'as' => 'section'],
        'selectedFieldId' => ['except' => null, 'as' => 'mID'],
    ];

    protected function getViewData(): array
    {
        $sections = $this->sectionsForView();
        $fields = $this->fieldsForView();

        if ($this->selectedFieldId === null && $fields !== []) {
            $this->selectedFieldId = (int) $fields[0]['id'];
        }

        return [
            'sections' => $sections,
            'fields' => $fields,
            'selectedField' => collect($fields)->first(fn (array $field): bool => (int) $field['id'] === (int) $this->selectedFieldId),
            'totalFields' => count($fields),
            'tableReady' => $this->tablesReady(),
        ];
    }

    public function selectField(int $id): void
    {
        if ($id > 0) {
            $this->selectedFieldId = $id;
        }
    }

    public function updatedSearch(): void
    {
        $this->selectedFieldId = null;
    }

    public function updatedSectionFilter(): void
    {
        $this->selectedFieldId = null;
    }

    public function toggleFieldFlag(int $id, string $flag, bool $value): void
    {
        if (! $this->tablesReady() || $id <= 0) {
            return;
        }

        $column = match ($flag) {
            'active' => 'is_active',
            'filter' => 'is_filterable',
            'multiple' => 'is_multiple',
            default => null,
        };

        if (! $column || ! Schema::hasColumn('product_extra_fields', $column)) {
            return;
        }

        DB::table('product_extra_fields')
            ->where('id', $id)
            ->update([
                $column => $value,
                'updated_at' => now(),
            ]);

        Notification::make()
            ->title($value ? 'Το πεδίο ενεργοποιήθηκε.' : 'Το πεδίο απενεργοποιήθηκε.')
            ->success()
            ->send();
    }

    protected function sectionsForView(): array
    {
        if (! Schema::hasTable('product_extra_field_sections')) {
            return [['id' => 'all', 'name' => '-Επιλέξτε τομέα-']];
        }

        return DB::table('product_extra_field_sections')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($section): array => [
                'id' => (string) $section->id,
                'name' => (string) $section->name,
            ])
            ->prepend(['id' => 'all', 'name' => '-Επιλέξτε τομέα-'])
            ->all();
    }

    protected function fieldsForView(): array
    {
        if (! $this->tablesReady()) {
            return $this->fallbackFields();
        }

        $query = DB::table('product_extra_fields as fields')
            ->leftJoin('product_extra_field_sections as sections', 'sections.id', '=', 'fields.section_id')
            ->select([
                'fields.*',
                'sections.name as section_name',
            ]);

        if ($this->sectionFilter !== 'all' && ctype_digit($this->sectionFilter)) {
            $query->where('fields.section_id', (int) $this->sectionFilter);
        }

        if (trim($this->search) !== '') {
            $search = '%' . trim($this->search) . '%';
            $query->where(function ($query) use ($search): void {
                $query->where('fields.name', 'like', $search)
                    ->orWhere('fields.internal_code', 'like', $search)
                    ->orWhere('fields.tags', 'like', $search)
                    ->orWhere('sections.name', 'like', $search);
            });
        }

        return $query
            ->orderBy('sections.sort_order')
            ->orderBy('fields.sort_order')
            ->orderBy('fields.name')
            ->limit(500)
            ->get()
            ->map(fn ($field): array => $this->mapField((array) $field))
            ->all();
    }

    protected function mapField(array $field): array
    {
        $id = (int) ($field['id'] ?? 0);

        return [
            'id' => $id,
            'name' => (string) ($field['name'] ?? '-'),
            'internal_code' => (string) ($field['internal_code'] ?? ''),
            'section' => (string) ($field['section_name'] ?? 'TECHNICAL DETAILS'),
            'is_active' => (bool) ($field['is_active'] ?? false),
            'is_filterable' => (bool) ($field['is_filterable'] ?? false),
            'is_multiple' => (bool) ($field['is_multiple'] ?? false),
            'field_type' => $this->fieldTypeLabel((string) ($field['field_type'] ?? 'text')),
            'tags' => (string) ($field['tags'] ?? ''),
            'products_count' => $this->productsCountForField($id),
            'sample_products' => $this->sampleProductsForField($id),
        ];
    }

    protected function productsCountForField(int $id): int
    {
        if ($id <= 0 || ! Schema::hasTable('product_extra_field_values')) {
            return 0;
        }

        try {
            return DB::table('product_extra_field_values')
                ->where('extra_field_id', $id)
                ->distinct('product_id')
                ->count('product_id');
        } catch (\Throwable) {
            return 0;
        }
    }

    protected function sampleProductsForField(int $id): array
    {
        if ($id <= 0 || ! Schema::hasTable('product_extra_field_values') || ! Schema::hasTable('products')) {
            return [];
        }

        try {
            return DB::table('product_extra_field_values as values')
                ->join('products', 'products.id', '=', 'values.product_id')
                ->where('values.extra_field_id', $id)
                ->select(['products.id', 'products.title', 'products.code'])
                ->limit(20)
                ->get()
                ->map(fn ($product): array => [
                    'id' => (int) $product->id,
                    'title' => (string) $product->title,
                    'code' => (string) ($product->code ?? ''),
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function fieldTypeLabel(string $type): string
    {
        return match ($type) {
            'number' => 'Αριθμός',
            'boolean' => 'Ναι / Όχι',
            'select' => 'Λίστα',
            default => 'Κείμενο',
        };
    }

    protected function tablesReady(): bool
    {
        return Schema::hasTable('product_extra_fields')
            && Schema::hasTable('product_extra_field_sections');
    }

    protected function fallbackFields(): array
    {
        return collect([
            'Accessory Type',
            'All in one',
            'Brand',
            'Case Type',
            'Connection Type',
            'CPU',
            'CPU Brand',
            'Data Cable Type',
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
            'Screen Size',
            'VRAM',
            'VRAM Type',
            'Wattage',
            'Resolution',
            'Interface / Connector',
            'USB Version',
            'Cable Length',
            'PoE',
            'Number of Ports',
            'Wi-Fi Standard',
            'Printer Type',
        ])->map(fn (string $name, int $index): array => [
            'id' => $index + 1,
            'name' => $name,
            'internal_code' => '',
            'section' => 'TECHNICAL DETAILS',
            'is_active' => $name !== 'Brand',
            'is_filterable' => $name !== 'Brand',
            'is_multiple' => in_array($name, ['Compatibility', 'Interface / Connector'], true),
            'field_type' => 'Κείμενο',
            'tags' => '',
            'products_count' => 0,
            'sample_products' => [],
        ])->all();
    }
}
