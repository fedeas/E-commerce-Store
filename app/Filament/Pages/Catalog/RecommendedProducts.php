<?php

namespace App\Filament\Pages\Catalog;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class RecommendedProducts extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Προτεινόμενα';
    protected static ?string $title = 'Προτεινόμενα';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-hand-thumb-up';
    protected static ?int $navigationSort = 30;

    protected string $view = 'filament.pages.catalog.recommended-products';

    public ?int $editingId = null;
    public string $productSearch = '';
    public string $productIdInput = '';
    public string $sectionTypeInput = 'recommended';
    public string $sectionTitleInput = 'Προτεινόμενα';
    public bool $sectionEnabledInput = true;
    public string $labelInput = '';
    public string $sortOrderInput = '0';
    public string $startsAtInput = '';
    public string $endsAtInput = '';
    public bool $isActiveInput = true;

    protected function getViewData(): array
    {
        return [
            'featured' => $this->featuredForView(),
            'products' => $this->productOptions(),
            'section' => $this->sectionForView(),
            'tableReady' => Schema::hasTable('featured_products'),
        ];
    }

    public function mount(): void
    {
        $section = $this->sectionSettings();
        $this->sectionTypeInput = (string) $section['item_type'];
        $this->sectionTitleInput = (string) $section['title'];
        $this->sectionEnabledInput = (bool) $section['is_active'];
    }

    public function createNew(): void
    {
        $this->resetForm();
    }

    public function useProduct(int $id): void
    {
        $this->productIdInput = (string) $id;
    }

    public function editFeatured(int $id): void
    {
        if (! Schema::hasTable('featured_products')) {
            return;
        }

        $featured = DB::table('featured_products')->where('id', $id)->first();

        if (! $featured) {
            return;
        }

        $this->editingId = (int) $featured->id;
        $this->productIdInput = (string) ($featured->product_id ?? '');
        $this->labelInput = (string) ($featured->label ?? '');
        $this->sortOrderInput = (string) ($featured->sort_order ?? 0);
        $this->startsAtInput = $this->dateTimeLocal($featured->starts_at ?? null);
        $this->endsAtInput = $this->dateTimeLocal($featured->ends_at ?? null);
        $this->isActiveInput = (bool) ($featured->is_active ?? true);
    }

    public function saveFeatured(): void
    {
        if (! Schema::hasTable('featured_products')) {
            Notification::make()->title('Τρέξε πρώτα τα migrations για προτεινόμενα.')->warning()->send();
            return;
        }

        $this->validate([
            'productIdInput' => ['required', 'integer'],
            'labelInput' => ['nullable', 'string', 'max:120'],
            'sortOrderInput' => ['nullable', 'integer'],
            'startsAtInput' => ['nullable', 'date'],
            'endsAtInput' => ['nullable', 'date'],
        ]);

        $data = [
            'product_id' => (int) $this->productIdInput,
            'source' => $this->sectionTypeInput,
            'label' => $this->emptyToNull($this->labelInput),
            'is_active' => $this->isActiveInput,
            'sort_order' => (int) ($this->sortOrderInput ?: 0),
            'starts_at' => $this->emptyToNull($this->startsAtInput),
            'ends_at' => $this->emptyToNull($this->endsAtInput),
            'updated_at' => now(),
        ];

        if ($this->editingId) {
            DB::table('featured_products')->where('id', $this->editingId)->update($data);
            $message = 'Το προτεινόμενο ενημερώθηκε.';
        } else {
            $data['created_at'] = now();
            $this->editingId = (int) DB::table('featured_products')->insertGetId($data);
            $message = 'Το προτεινόμενο προστέθηκε.';
        }

        Notification::make()->title($message)->success()->send();
    }

    public function saveSection(): void
    {
        if (! Schema::hasTable('home_product_sections')) {
            Notification::make()->title('Τρέξε πρώτα τα migrations για το section αρχικής.')->warning()->send();
            return;
        }

        $this->validate([
            'sectionTypeInput' => ['required', 'in:recommended,offers'],
            'sectionTitleInput' => ['nullable', 'string', 'max:120'],
        ]);

        $data = [
            'title' => $this->emptyToNull($this->sectionTitleInput) ?? $this->sourceLabel($this->sectionTypeInput),
            'item_type' => $this->sectionTypeInput,
            'is_active' => $this->sectionEnabledInput,
            'updated_at' => now(),
        ];

        $exists = DB::table('home_product_sections')
            ->where('section_key', 'home_featured_products')
            ->exists();

        if ($exists) {
            DB::table('home_product_sections')
                ->where('section_key', 'home_featured_products')
                ->update($data);
        } else {
            DB::table('home_product_sections')->insert($data + [
                'section_key' => 'home_featured_products',
                'created_at' => now(),
                'sort_order' => 0,
            ]);
        }

        if (Schema::hasTable('featured_products')) {
            DB::table('featured_products')->update(['source' => $this->sectionTypeInput, 'updated_at' => now()]);
        }

        Notification::make()->title('Το section αρχικής ενημερώθηκε.')->success()->send();
    }

    public function deleteFeatured(int $id): void
    {
        if (Schema::hasTable('featured_products') && $id > 0) {
            DB::table('featured_products')->where('id', $id)->delete();
        }

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function toggleFeatured(int $id, bool $active): void
    {
        if (Schema::hasTable('featured_products') && $id > 0) {
            DB::table('featured_products')->where('id', $id)->update(['is_active' => $active, 'updated_at' => now()]);
        }
    }

    protected function featuredForView(): array
    {
        if (! Schema::hasTable('featured_products')) {
            return [];
        }

        return DB::table('featured_products')
            ->leftJoin('products', 'products.id', '=', 'featured_products.product_id')
            ->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id')
            ->select('featured_products.*', 'products.code', 'products.title', 'products.main_image', 'product_prices.retail_inc_vat', 'product_prices.discount_inc_vat')
            ->orderBy('featured_products.sort_order')
            ->orderByDesc('featured_products.id')
            ->get()
            ->map(fn ($item): array => [
                'id' => (int) $item->id,
                'product' => trim(($item->code ? $item->code . ' - ' : '') . (string) ($item->title ?? '')),
                'image' => $this->imageUrl($item->main_image ?? null),
                'source' => $this->sourceLabel((string) ($item->source ?? $this->sectionTypeInput)),
                'label' => (string) ($item->label ?? ''),
                'price' => $this->money($item->discount_inc_vat ?? $item->retail_inc_vat ?? null),
                'sort_order' => (int) ($item->sort_order ?? 0),
                'is_active' => (bool) ($item->is_active ?? false),
                'period' => $this->periodLabel($item->starts_at ?? null, $item->ends_at ?? null),
            ])
            ->all();
    }

    protected function productOptions(): array
    {
        if (! Schema::hasTable('products')) {
            return [];
        }

        $query = DB::table('products')
            ->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id')
            ->select('products.id', 'products.code', 'products.title', 'product_prices.retail_inc_vat', 'product_prices.discount_inc_vat')
            ->orderByDesc('products.id')
            ->limit(30);

        if (trim($this->productSearch) !== '') {
            $search = '%' . trim($this->productSearch) . '%';
            $query->where(function ($query) use ($search): void {
                $query->where('products.title', 'like', $search)
                    ->orWhere('products.code', 'like', $search)
                    ->orWhere('products.mpn', 'like', $search)
                    ->orWhere('products.ean', 'like', $search);
            });
        }

        return $query->get()->map(fn ($product): array => [
            'id' => (int) $product->id,
            'title' => (string) ($product->title ?? ''),
            'code' => (string) ($product->code ?? ''),
            'price' => $this->money($product->discount_inc_vat ?? $product->retail_inc_vat ?? null),
        ])->all();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->productIdInput = '';
        $this->labelInput = '';
        $this->sortOrderInput = '0';
        $this->startsAtInput = '';
        $this->endsAtInput = '';
        $this->isActiveInput = true;
    }

    protected function sourceLabel(string $source): string
    {
        return match ($source) {
            'offers', 'offer' => 'Προσφορές',
            'bestseller' => 'Bestseller',
            default => 'Προτεινόμενα',
        };
    }

    protected function sectionForView(): array
    {
        $section = $this->sectionSettings();

        return [
            'title' => (string) $section['title'],
            'item_type' => (string) $section['item_type'],
            'type_label' => $this->sourceLabel((string) $section['item_type']),
            'is_active' => (bool) $section['is_active'],
        ];
    }

    protected function sectionSettings(): array
    {
        if (! Schema::hasTable('home_product_sections')) {
            return [
                'title' => 'Προτεινόμενα',
                'item_type' => 'recommended',
                'is_active' => true,
            ];
        }

        $row = DB::table('home_product_sections')
            ->where('section_key', 'home_featured_products')
            ->first();

        return [
            'title' => (string) ($row->title ?? 'Προτεινόμενα'),
            'item_type' => (string) ($row->item_type ?? 'recommended'),
            'is_active' => (bool) ($row->is_active ?? true),
        ];
    }

    protected function imageUrl(?string $path): string
    {
        if (! $path) {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    protected function money($value): string
    {
        return $value === null ? '-' : number_format((float) $value, 2, ',', '.') . ' €';
    }

    protected function periodLabel($startsAt, $endsAt): string
    {
        return ($startsAt ? date('d/m/Y H:i', strtotime((string) $startsAt)) : 'Άμεσα') . ' - ' . ($endsAt ? date('d/m/Y H:i', strtotime((string) $endsAt)) : 'Χωρίς λήξη');
    }

    protected function dateTimeLocal($value): string
    {
        return $value ? date('Y-m-d\TH:i', strtotime((string) $value)) : '';
    }

    protected function emptyToNull(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }
}
