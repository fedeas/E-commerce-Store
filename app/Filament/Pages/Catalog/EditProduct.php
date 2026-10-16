<?php

namespace App\Filament\Pages\Catalog;

use App\Models\Product;
use App\Support\LocalMediaStore;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class EditProduct extends Page
{
    protected ?LocalMediaStore $mediaStore = null;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'catalog/products/{product}/edit';

    protected string $view = 'filament.pages.catalog.edit-product';

    
    public string $returnUrl = '';
public ?int $productId = null;

    public ?int $categoryId = null;

    public ?int $brandId = null;

    public ?int $supplierId = null;

    public ?int $legacyId = null;

    public string $productTitle = '';

    public string $code = '';

    public string $mpn = '';

    public string $ean = '';

    public string $status = 'active';

    public bool $active = true;

    public bool $boxnow = false;

    public bool $article11 = false;

    public bool $shippingDisabled = false;

    public bool $statusUpdateEnabled = true;

    public bool $purchasePriceUpdateEnabled = true;

    public bool $priceUpdateEnabled = true;

    public string $vatRate = '19';

    public string $costExVat = '';

    public string $retailIncVat = '';

    public string $discountIncVat = '';

    public string $skroutzIncVat = '';

    public string $wholesaleIncVat = '';

    public string $silverIncVat = '';

    public string $goldIncVat = '';

    public string $minQuantity = '1';

    public string $setQuantity = '1';

    public string $availableFrom = '';

    public string $stockText = '';

    public string $addonPriceFixed = '0';

    public string $addonPricePercent = '0';

    public string $minimumPriceIncVat = '0';

    public string $dimensions = '';

    public string $weightKg = '';

    public string $imagePath = '';

    public string $mainImage = '';

    public string $galleryImages = '';

    public string $youtubeUrl = '';

    public string $shortDescription = '';

    public string $description = '';

    public string $specs = '';

    public string $metaDescription = '';

    public string $metaKeywords = '';

    public array $brands = [];

    public array $suppliers = [];

    public array $categories = [];

    public function mount(int|string $product): void
    {
        $this->returnUrl = $this->safeReturnUrl((string) request()->query('return', ''));
        $this->productId = (int) $product;

        $this->loadOptions();
        $this->loadProduct();
    }

    public function getTitle(): string
    {
        return 'Επεξεργασία Προϊόντος';
    }
    public function cancelAndReturn()
    {
        return redirect()->to($this->returnUrl !== '' ? $this->returnUrl : CategoriesProducts::getUrl());
    }

    public function backToCatalog()
    {
        return $this->cancelAndReturn();
    }

    private function safeReturnUrl(string $url): string
    {
        $url = trim(rawurldecode($url));

        if ($url === '') {
            return CategoriesProducts::getUrl();
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        if (str_starts_with($url, url('/'))) {
            return $url;
        }

        return CategoriesProducts::getUrl();
    }





    public function save(): void
    {
        if (! $this->productId || ! Schema::hasTable('products')) {
            return;
        }

        $productData = [];

        $this->putProductValue($productData, 'legacy_id', $this->legacyId);
        $this->putProductValue($productData, 'category_id', $this->categoryId);
        $this->putProductValue($productData, 'brand_id', $this->brandId);
        $this->putProductValue($productData, 'primary_supplier_id', $this->supplierId);
        $this->putProductValue($productData, 'title', $this->productTitle);
        $this->putProductValue($productData, 'code', $this->emptyToNull($this->code));
        $this->putProductValue($productData, 'mpn', $this->emptyToNull($this->mpn));
        $this->putProductValue($productData, 'ean', $this->emptyToNull($this->ean));

        if ($this->hasColumn('products', 'active')) {
            $productData['active'] = $this->active;
        }

        if ($this->hasColumn('products', 'status')) {
            $productData['status'] = $this->active ? 'active' : 'inactive';
        }

        $this->putProductValue($productData, 'boxnow_enabled', $this->boxnow);
        $this->putProductValue($productData, 'article_11_enabled', $this->article11);
        $this->putProductValue($productData, 'shipping_disabled', $this->shippingDisabled);
        $this->putProductValue($productData, 'status_update_enabled', $this->statusUpdateEnabled);
        $this->putProductValue($productData, 'purchase_price_update_enabled', $this->purchasePriceUpdateEnabled);
        $this->putProductValue($productData, 'price_update_enabled', $this->priceUpdateEnabled);

        $this->putProductValue($productData, 'vat_rate', $this->decimalOrNull($this->vatRate));
        $this->putProductValue($productData, 'min_quantity', $this->decimalOrNull($this->minQuantity));
        $this->putProductValue($productData, 'set_quantity', $this->decimalOrNull($this->setQuantity));
        $this->putProductValue($productData, 'available_from', $this->emptyToNull($this->availableFrom));
        $this->putProductValue($productData, 'stock_text', $this->emptyToNull($this->stockText));
        $this->putProductValue($productData, 'addon_price_fixed', $this->decimalOrNull($this->addonPriceFixed));
        $this->putProductValue($productData, 'addon_price_percent', $this->decimalOrNull($this->addonPricePercent));
        $this->putProductValue($productData, 'minimum_price_inc_vat', $this->decimalOrNull($this->minimumPriceIncVat));
        $this->putProductValue($productData, 'dimensions', $this->emptyToNull($this->dimensions));
        $this->putProductValue($productData, 'weight_kg', $this->decimalOrNull($this->weightKg));
        $this->putProductValue($productData, 'image_path', $this->localMediaOrNull($this->imagePath, 'product-images/' . $this->productId));
        $this->putProductValue($productData, 'main_image', $this->localMediaOrNull($this->mainImage, 'product-images/' . $this->productId));
        $this->putProductValue($productData, 'youtube_url', $this->emptyToNull($this->youtubeUrl));
        $this->putProductValue($productData, 'short_description', $this->emptyToNull($this->shortDescription));
        $this->putProductValue($productData, 'description', $this->emptyToNull($this->description));
        $this->putProductValue($productData, 'specs', $this->emptyToNull($this->specs));
        $this->putProductValue($productData, 'meta_description', $this->emptyToNull($this->metaDescription));
        $this->putProductValue($productData, 'meta_keywords', $this->emptyToNull($this->metaKeywords));

        if ($this->hasColumn('products', 'updated_at')) {
            $productData['updated_at'] = now();
        }

        DB::table('products')
            ->where('id', $this->productId)
            ->update($productData);

        $this->savePrices();
        $this->saveGalleryImages();

        Notification::make()
            ->title('Το προϊόν αποθηκεύτηκε.')
            ->success()
            ->send();

        $this->loadProduct();
    }

    public function deleteProduct(): void
    {
        if (! $this->productId || ! Schema::hasTable('products')) {
            return;
        }

        if ($this->hasColumn('products', 'deleted_at')) {
            DB::table('products')
                ->where('id', $this->productId)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('products')
                ->where('id', $this->productId)
                ->delete();
        }

        Notification::make()
            ->title('Το προϊόν διαγράφηκε.')
            ->success()
            ->send();

        $this->redirect(CategoriesProducts::getUrl(), navigate: false);
    }

    protected function loadProduct(): void
    {
        $product = DB::table('products')
            ->where('id', $this->productId)
            ->first();

        if (! $product) {
            abort(404);
        }

        $this->legacyId = $this->value($product, 'legacy_id');
        $this->categoryId = $this->value($product, 'category_id');
        $this->brandId = $this->value($product, 'brand_id');
        $this->supplierId = $this->value($product, 'primary_supplier_id');

        $this->productTitle = (string) ($this->value($product, 'title') ?? '');
        $this->code = (string) ($this->value($product, 'code') ?? '');
        $this->mpn = (string) ($this->value($product, 'mpn') ?? '');
        $this->ean = (string) ($this->value($product, 'ean') ?? '');

        $status = (string) ($this->value($product, 'status') ?? 'active');
        $activeValue = $this->value($product, 'active');

        $this->active = $this->boolValue($activeValue, in_array($status, ['active', 'enabled', 'yes', '1'], true));
        $this->boxnow = $this->boolValue($this->value($product, 'boxnow_enabled'), false);
        $this->article11 = $this->boolValue($this->value($product, 'article_11_enabled'), false);
        $this->shippingDisabled = $this->boolValue($this->value($product, 'shipping_disabled'), false);
        $this->statusUpdateEnabled = $this->boolValue($this->value($product, 'status_update_enabled'), true);
        $this->purchasePriceUpdateEnabled = $this->boolValue($this->value($product, 'purchase_price_update_enabled'), true);
        $this->priceUpdateEnabled = $this->boolValue($this->value($product, 'price_update_enabled'), true);

        $this->vatRate = $this->stringValue($this->value($product, 'vat_rate'), '19');
        $this->minQuantity = $this->stringValue($this->value($product, 'min_quantity'), '1');
        $this->setQuantity = $this->stringValue($this->value($product, 'set_quantity'), '1');
        $this->availableFrom = $this->stringValue($this->value($product, 'available_from'), '');
        $this->stockText = $this->stringValue($this->value($product, 'stock_text'), '');
        $this->addonPriceFixed = $this->stringValue($this->value($product, 'addon_price_fixed'), '0');
        $this->addonPricePercent = $this->stringValue($this->value($product, 'addon_price_percent'), '0');
        $this->minimumPriceIncVat = $this->stringValue($this->value($product, 'minimum_price_inc_vat'), '0');
        $this->dimensions = $this->stringValue($this->value($product, 'dimensions'), '');
        $this->weightKg = $this->stringValue($this->value($product, 'weight_kg'), '');
        $this->imagePath = $this->stringValue($this->value($product, 'image_path'), '');
        $this->mainImage = $this->stringValue($this->value($product, 'main_image'), '');
        $this->galleryImages = $this->loadGalleryImages();
        $this->youtubeUrl = $this->stringValue($this->value($product, 'youtube_url'), '');
        $this->shortDescription = $this->stringValue($this->value($product, 'short_description'), '');
        $this->description = $this->stringValue($this->value($product, 'description'), '');
        $this->specs = $this->stringValue($this->value($product, 'specs'), '');
        $this->metaDescription = $this->stringValue($this->value($product, 'meta_description'), '');
        $this->metaKeywords = $this->stringValue($this->value($product, 'meta_keywords'), '');

        $prices = Schema::hasTable('product_prices')
            ? DB::table('product_prices')->where('product_id', $this->productId)->first()
            : null;

        $this->costExVat = $this->stringValue($prices?->cost_ex_vat ?? null, '');
        $this->retailIncVat = $this->stringValue($prices?->retail_inc_vat ?? null, '');
        $this->discountIncVat = $this->stringValue($prices?->discount_inc_vat ?? null, '');
        $this->skroutzIncVat = $this->stringValue($prices?->skroutz_inc_vat ?? null, '');
        $this->wholesaleIncVat = $this->stringValue($prices?->wholesale_inc_vat ?? null, '');
        $this->silverIncVat = $this->stringValue($prices?->silver_inc_vat ?? null, '');
        $this->goldIncVat = $this->stringValue($prices?->gold_inc_vat ?? null, '');
    }

    protected function loadOptions(): void
    {
        $this->brands = Schema::hasTable('brands')
            ? DB::table('brands')->orderBy('name')->pluck('name', 'id')->toArray()
            : [];

        $this->suppliers = Schema::hasTable('suppliers')
            ? DB::table('suppliers')->orderBy('name')->pluck('name', 'id')->toArray()
            : [];

        $this->categories = Schema::hasTable('categories')
            ? DB::table('categories')->orderBy('full_path')->orderBy('name')->get()->mapWithKeys(function ($category): array {
                $label = $category->full_path ?? $category->name ?? ('Category #' . $category->id);

                return [$category->id => $label];
            })->toArray()
            : [];
    }

    protected function savePrices(): void
    {
        if (! Schema::hasTable('product_prices') || ! $this->hasColumn('product_prices', 'product_id')) {
            return;
        }

        $existing = DB::table('product_prices')
            ->where('product_id', $this->productId)
            ->first();

        $data = [
            'product_id' => $this->productId,
        ];

        $this->putPriceValue($data, 'cost_ex_vat', $this->decimalOrNull($this->costExVat));
        $this->putPriceValue($data, 'retail_inc_vat', $this->decimalOrNull($this->retailIncVat));
        $this->putPriceValue($data, 'discount_inc_vat', $this->decimalOrNull($this->discountIncVat));
        $this->putPriceValue($data, 'skroutz_inc_vat', $this->decimalOrNull($this->skroutzIncVat));
        $this->putPriceValue($data, 'wholesale_inc_vat', $this->decimalOrNull($this->wholesaleIncVat));
        $this->putPriceValue($data, 'silver_inc_vat', $this->decimalOrNull($this->silverIncVat));
        $this->putPriceValue($data, 'gold_inc_vat', $this->decimalOrNull($this->goldIncVat));

        if ($this->hasColumn('product_prices', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if ($existing) {
            DB::table('product_prices')
                ->where('id', $existing->id)
                ->update($data);

            return;
        }

        if ($this->hasColumn('product_prices', 'created_at')) {
            $data['created_at'] = now();
        }

        DB::table('product_prices')->insert($data);
    }

    protected function loadGalleryImages(): string
    {
        if (! $this->productId || ! Schema::hasTable('product_images')) {
            return '';
        }

        return DB::table('product_images')
            ->where('product_id', $this->productId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($image): string => $this->mediaStore()->publicUrl((string) ($image->path ?: $image->url ?: '')))
            ->filter()
            ->implode("\n");
    }

    protected function saveGalleryImages(): void
    {
        if (! $this->productId || ! Schema::hasTable('product_images')) {
            return;
        }

        $urls = collect(preg_split('/[\r\n]+/', $this->galleryImages) ?: [])
            ->map(fn (string $url): string => trim($url))
            ->filter()
            ->unique()
            ->values();

        DB::table('product_images')
            ->where('product_id', $this->productId)
            ->where('source', 'manual')
            ->delete();

        $now = now();
        $rows = $urls
            ->map(function (string $url, int $index) use ($now): array {
                $path = $this->mediaStore()->localize($url, 'product-images/' . $this->productId);

                return [
                    'product_id' => $this->productId,
                    'url' => $this->mediaStore()->isRemote($url) ? $url : null,
                    'path' => $this->mediaStore()->isRemote($path) ? null : $path,
                    'source' => 'manual',
                    'sort_order' => ($index + 1) * 10,
                    'is_primary' => $index === 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter(fn (array $row): bool => (string) ($row['path'] ?? $row['url'] ?? '') !== '')
            ->unique(fn (array $row): string => (string) ($row['path'] ?? $row['url']))
            ->values()
            ->all();

        if ($rows !== []) {
            DB::table('product_images')->insert($rows);

            $first = $rows[0]['path'] ?? $rows[0]['url'];
            $productData = [];
            $this->putProductValue($productData, 'image_path', $first);
            $this->putProductValue($productData, 'main_image', $first);

            if ($productData !== []) {
                if ($this->hasColumn('products', 'updated_at')) {
                    $productData['updated_at'] = now();
                }

                DB::table('products')->where('id', $this->productId)->update($productData);
            }
        }
    }

    protected function putProductValue(array &$data, string $column, mixed $value): void
    {
        if ($this->hasColumn('products', $column)) {
            $data[$column] = $value;
        }
    }

    protected function localMediaOrNull(string $value, string $directory): ?string
    {
        $value = $this->emptyToNull($value);

        return $value === null ? null : $this->mediaStore()->localize($value, $directory);
    }

    protected function mediaStore(): LocalMediaStore
    {
        return $this->mediaStore ??= new LocalMediaStore();
    }

    protected function putPriceValue(array &$data, string $column, mixed $value): void
    {
        if ($this->hasColumn('product_prices', $column)) {
            $data[$column] = $value;
        }
    }

    protected function hasColumn(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    protected function value(object $row, string $column): mixed
    {
        return property_exists($row, $column) ? $row->{$column} : null;
    }

    protected function boolValue(mixed $value, bool $default): bool
    {
        if ($value === null) {
            return $default;
        }

        return in_array($value, [true, 1, '1', 'true', 'yes', 'on', 'active', 'enabled'], true);
    }

    protected function stringValue(mixed $value, string $default): string
    {
        if ($value === null) {
            return $default;
        }

        return (string) $value;
    }

    protected function decimalOrNull(string $value): ?float
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(['€', ' '], '', $value);
        $value = preg_replace('/[^\d,.\-]/', '', $value);

        if ($value === '' || $value === '-' || $value === ',' || $value === '.') {
            return null;
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($lastComma !== false) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

    protected function emptyToNull(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
