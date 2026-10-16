<?php

namespace App\Filament\Pages\Catalog;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;
use UnitEnum;

class Offers extends Page
{
    use WithFileUploads;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Προσφορές';
    protected static ?string $title = 'Προσφορές';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?int $navigationSort = 28;

    protected string $view = 'filament.pages.catalog.offers';

    public ?int $editingId = null;
    public string $productSearch = '';
    public string $productIdInput = '';
    public string $titleInput = '';
    public string $badgeInput = 'Προσφορά';
    public string $descriptionInput = '';
    public string $linkUrlInput = '';
    public string $regularPriceInput = '';
    public string $discountPercentInput = '';
    public string $offerPriceInput = '';
    public string $sortOrderInput = '0';
    public string $startsAtInput = '';
    public string $endsAtInput = '';
    public bool $isActiveInput = true;
    public $imageUpload = null;

    protected function getViewData(): array
    {
        return [
            'offers' => $this->offersForView(),
            'products' => $this->productOptions(),
            'tableReady' => Schema::hasTable('shop_offers'),
        ];
    }

    public function createNew(): void
    {
        $this->resetForm();
    }

    public function updatedRegularPriceInput(): void
    {
        $this->refreshCalculatedOfferPrice();
    }

    public function updatedDiscountPercentInput(): void
    {
        $this->refreshCalculatedOfferPrice();
    }

    public function useProduct(int $id): void
    {
        $product = $this->productRow($id);

        if (! $product) {
            return;
        }

        $this->productIdInput = (string) $id;
        $this->titleInput = (string) ($product->title ?? '');
        $this->regularPriceInput = $this->decimalString($product->retail_inc_vat ?? null);
        $this->offerPriceInput = $this->decimalString($product->discount_inc_vat ?? null);
        $this->discountPercentInput = $this->discountPercentFromPrices($this->regularPriceInput, $this->offerPriceInput);
        $this->linkUrlInput = '/product/' . ($product->slug ?: $id);
    }

    public function editOffer(int $id): void
    {
        if (! Schema::hasTable('shop_offers')) {
            return;
        }

        $offer = DB::table('shop_offers')->where('id', $id)->first();

        if (! $offer) {
            return;
        }

        $this->editingId = (int) $offer->id;
        $this->productIdInput = (string) ($offer->product_id ?? '');
        $this->titleInput = (string) ($offer->title ?? '');
        $this->badgeInput = (string) ($offer->badge ?? '');
        $this->descriptionInput = (string) ($offer->description ?? '');
        $this->linkUrlInput = (string) ($offer->link_url ?? '');
        $this->regularPriceInput = $this->decimalString($offer->regular_price ?? null);
        $this->discountPercentInput = $this->decimalString(property_exists($offer, 'discount_percent') ? $offer->discount_percent : null);
        $this->offerPriceInput = $this->decimalString($offer->offer_price ?? null);
        $this->sortOrderInput = (string) ($offer->sort_order ?? 0);
        $this->startsAtInput = $this->dateTimeLocal($offer->starts_at ?? null);
        $this->endsAtInput = $this->dateTimeLocal($offer->ends_at ?? null);
        $this->isActiveInput = (bool) ($offer->is_active ?? true);
        $this->imageUpload = null;
    }

    public function saveOffer(): void
    {
        if (! Schema::hasTable('shop_offers')) {
            Notification::make()->title('Τρέξε πρώτα τα migrations για προσφορές.')->warning()->send();
            return;
        }

        $this->validate([
            'titleInput' => ['required', 'string', 'max:255'],
            'badgeInput' => ['nullable', 'string', 'max:80'],
            'descriptionInput' => ['nullable', 'string', 'max:1000'],
            'linkUrlInput' => ['nullable', 'string', 'max:255'],
            'regularPriceInput' => ['nullable', 'numeric'],
            'discountPercentInput' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'offerPriceInput' => ['nullable', 'numeric'],
            'sortOrderInput' => ['nullable', 'integer'],
            'startsAtInput' => ['nullable', 'date'],
            'endsAtInput' => ['nullable', 'date'],
            'imageUpload' => ['nullable', 'image', 'max:20480'],
        ]);

        $existing = $this->editingId ? DB::table('shop_offers')->where('id', $this->editingId)->first() : null;
        $imagePath = $existing->image_path ?? null;

        if ($this->imageUpload) {
            $imagePath = $this->imageUpload->store('shop-offers', 'public');
        }

        $offerPrice = $this->calculatedOfferPrice();

        $data = [
            'product_id' => $this->productIdInput !== '' ? (int) $this->productIdInput : null,
            'title' => trim($this->titleInput),
            'badge' => $this->emptyToNull($this->badgeInput),
            'description' => $this->emptyToNull($this->descriptionInput),
            'image_path' => $imagePath,
            'link_url' => $this->emptyToNull($this->linkUrlInput),
            'regular_price' => $this->decimalOrNull($this->regularPriceInput),
            'offer_price' => $offerPrice,
            'is_active' => $this->isActiveInput,
            'sort_order' => (int) ($this->sortOrderInput ?: 0),
            'starts_at' => $this->emptyToNull($this->startsAtInput),
            'ends_at' => $this->emptyToNull($this->endsAtInput),
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('shop_offers', 'discount_percent')) {
            $data['discount_percent'] = $this->decimalOrNull($this->discountPercentInput);
        }

        if ($this->editingId) {
            DB::table('shop_offers')->where('id', $this->editingId)->update($data);
            $message = 'Η προσφορά ενημερώθηκε.';
        } else {
            $data['created_at'] = now();
            $this->editingId = (int) DB::table('shop_offers')->insertGetId($data);
            $message = 'Η προσφορά προστέθηκε.';
        }

        $this->imageUpload = null;

        Notification::make()->title($message)->success()->send();
    }

    public function deleteOffer(int $id): void
    {
        if (Schema::hasTable('shop_offers') && $id > 0) {
            DB::table('shop_offers')->where('id', $id)->delete();
        }

        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function toggleOffer(int $id, bool $active): void
    {
        if (Schema::hasTable('shop_offers') && $id > 0) {
            DB::table('shop_offers')->where('id', $id)->update(['is_active' => $active, 'updated_at' => now()]);
        }
    }

    protected function offersForView(): array
    {
        if (! Schema::hasTable('shop_offers')) {
            return [];
        }

        return DB::table('shop_offers')
            ->leftJoin('products', 'products.id', '=', 'shop_offers.product_id')
            ->select('shop_offers.*', 'products.code as product_code', 'products.title as product_title')
            ->orderBy('shop_offers.sort_order')
            ->orderByDesc('shop_offers.id')
            ->get()
            ->map(fn ($offer): array => [
                'id' => (int) $offer->id,
                'title' => (string) $offer->title,
                'badge' => (string) ($offer->badge ?? ''),
                'product' => trim(($offer->product_code ? $offer->product_code . ' - ' : '') . (string) ($offer->product_title ?? '')),
                'image' => $offer->image_path ? Storage::disk('public')->url($offer->image_path) : '',
                'regular_price' => $this->money($offer->regular_price ?? null),
                'discount_percent' => $this->percent(property_exists($offer, 'discount_percent') ? $offer->discount_percent : null),
                'offer_price' => $this->money($offer->offer_price ?? null),
                'sort_order' => (int) ($offer->sort_order ?? 0),
                'is_active' => (bool) ($offer->is_active ?? false),
                'period' => $this->periodLabel($offer->starts_at ?? null, $offer->ends_at ?? null),
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
            ->select('products.id', 'products.code', 'products.title', 'products.slug', 'product_prices.retail_inc_vat', 'product_prices.discount_inc_vat')
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
            'retail' => $this->money($product->retail_inc_vat ?? null),
            'discount' => $this->money($product->discount_inc_vat ?? null),
        ])->all();
    }

    protected function productRow(int $id): ?object
    {
        if (! Schema::hasTable('products')) {
            return null;
        }

        return DB::table('products')
            ->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id')
            ->select('products.id', 'products.title', 'products.slug', 'product_prices.retail_inc_vat', 'product_prices.discount_inc_vat')
            ->where('products.id', $id)
            ->first();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->productIdInput = '';
        $this->titleInput = '';
        $this->badgeInput = 'Προσφορά';
        $this->descriptionInput = '';
        $this->linkUrlInput = '';
        $this->regularPriceInput = '';
        $this->discountPercentInput = '';
        $this->offerPriceInput = '';
        $this->sortOrderInput = '0';
        $this->startsAtInput = '';
        $this->endsAtInput = '';
        $this->isActiveInput = true;
        $this->imageUpload = null;
    }

    protected function money($value): string
    {
        return $value === null ? '-' : number_format((float) $value, 2, ',', '.') . ' €';
    }

    protected function decimalString($value): string
    {
        return $value === null ? '' : rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
    }

    protected function decimalOrNull(string $value): ?float
    {
        $value = str_replace(',', '.', trim($value));
        return $value === '' ? null : (float) $value;
    }

    protected function calculatedOfferPrice(): ?float
    {
        $regular = $this->decimalOrNull($this->regularPriceInput);
        $percent = $this->decimalOrNull($this->discountPercentInput);

        if ($regular !== null && $percent !== null) {
            $price = $regular * (100 - max(0, min(100, $percent))) / 100;
            $this->offerPriceInput = $this->decimalString($price);

            return round($price, 4);
        }

        return $this->decimalOrNull($this->offerPriceInput);
    }

    protected function refreshCalculatedOfferPrice(): void
    {
        $regular = $this->decimalOrNull($this->regularPriceInput);
        $percent = $this->decimalOrNull($this->discountPercentInput);

        if ($regular === null || $percent === null) {
            return;
        }

        $price = $regular * (100 - max(0, min(100, $percent))) / 100;
        $this->offerPriceInput = $this->decimalString($price);
    }

    protected function discountPercentFromPrices(string $regularInput, string $offerInput): string
    {
        $regular = $this->decimalOrNull($regularInput);
        $offer = $this->decimalOrNull($offerInput);

        if ($regular === null || $regular <= 0 || $offer === null) {
            return '';
        }

        return $this->decimalString((1 - ($offer / $regular)) * 100);
    }

    protected function percent($value): string
    {
        return $value === null ? '-' : rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',') . '%';
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
