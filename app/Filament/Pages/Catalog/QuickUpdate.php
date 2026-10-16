<?php

namespace App\Filament\Pages\Catalog;


use App\Support\CatalogProductsExcelExporter;use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZipArchive;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Filament\Pages\Page;
use BackedEnum;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class QuickUpdate extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Γρήγορη ανανέωση';
    protected static ?string $title = 'Γρήγορη ανανέωση';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?int $navigationSort = 16;

    protected string $view = 'filament.pages.catalog.quick-update';

    protected array $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'categoryFilter' => ['except' => 'all', 'as' => 'cat'],
        'brandFilter' => ['except' => 'all', 'as' => 'brand'],
        'supplierFilter' => ['except' => 'all', 'as' => 'supplier'],
        'notSupplierFilter' => ['except' => 'all', 'as' => 'notSupplier'],
        'xmlSourceFilter' => ['except' => 'all', 'as' => 'xml'],
        'statusFilter' => ['except' => 'all', 'as' => 'status'],
        'productPage' => ['except' => 1, 'as' => 'page'],
        'perPage' => ['except' => '50', 'as' => 'perPage'],
    ];

    public string $search = '';

    public string $categoryFilter = 'all';

    public string $brandFilter = 'all';

    public string $supplierFilter = 'all';

    public string $notSupplierFilter = 'all';

    public string $xmlSourceFilter = 'all';

    public string $statusFilter = 'all';

    public string $supplierLinkedFilter = 'all';

    public string $priceUpdateFilter = 'all';

    public string $purchasePriceUpdateFilter = 'all';

    public string $statusUpdateFilter = 'all';

    public string $pricePresenceFilter = 'all';

    public string $stockFilter = 'all';

    public string $stockTextFilter = 'all';

    public string $miscellaneousFilter = 'all';

    public string $perPage = '50';

    public int $productPage = 1;

    public int $totalProducts = 0;

    public int $productFirstItem = 0;

    public int $productLastItem = 0;

    public int $productLastPage = 1;

    public int $productCurrentPage = 1;

    public int $perPageValue = 50;

    public array $selectedProductIds = [];

    protected function getViewData(): array
    {
        $products = $this->getProductsForView();
        $pageProductIds = array_map(
            fn (array $product): int => (int) $product['id'],
            $products,
        );

        return [
            'products' => $products,
            'pageProductIds' => $pageProductIds,
            'allPageSelected' => $pageProductIds !== []
                && empty(array_diff($pageProductIds, $this->normalizedSelectedIds())),
            'selectedCount' => count($this->normalizedSelectedIds()),
            'categories' => $this->getCategoryOptions(),
            'brands' => $this->getBrandOptions(),
            'suppliers' => $this->getSupplierOptions(),
            'stockTexts' => $this->getStockTextOptions(),
            'totalProducts' => $this->totalProducts,
            'productFirstItem' => $this->productFirstItem,
            'productLastItem' => $this->productLastItem,
            'productLastPage' => $this->productLastPage,
            'productCurrentPage' => $this->productCurrentPage,
            'perPageValue' => $this->perPageValue,
        ];
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->categoryFilter = 'all';
        $this->brandFilter = 'all';
        $this->supplierFilter = 'all';
        $this->notSupplierFilter = 'all';
        $this->xmlSourceFilter = 'all';
        $this->statusFilter = 'all';
        $this->supplierLinkedFilter = 'all';
        $this->priceUpdateFilter = 'all';
        $this->purchasePriceUpdateFilter = 'all';
        $this->statusUpdateFilter = 'all';
        $this->pricePresenceFilter = 'all';
        $this->stockFilter = 'all';
        $this->stockTextFilter = 'all';
        $this->miscellaneousFilter = 'all';
        $this->selectedProductIds = [];
        $this->productPage = 1;
    }

    public function updatedSearch(): void
    {
        $this->resetListingPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedBrandFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedSupplierFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedNotSupplierFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedXmlSourceFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedSupplierLinkedFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedPriceUpdateFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedPurchasePriceUpdateFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedStatusUpdateFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedPricePresenceFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedStockFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedStockTextFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedMiscellaneousFilter(): void
    {
        $this->resetListingPage();
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, ['25', '50', '100', '300'], true)) {
            $this->perPage = '50';
        }

        $this->resetListingPage();
    }

    public function previousProductPage(): void
    {
        if ($this->productPage > 1) {
            $this->productPage--;
        }
    }

    public function nextProductPage(): void
    {
        if ($this->productPage < $this->productLastPage) {
            $this->productPage++;
        }
    }

    public function gotoProductPage(int $page): void
    {
        $this->productPage = max(1, $page);
    }

    public function togglePageSelection(): void
    {
        $pageIds = $this->getCurrentPageProductIds();
        $selectedIds = $this->normalizedSelectedIds();

        if ($pageIds === []) {
            return;
        }

        if (empty(array_diff($pageIds, $selectedIds))) {
            $this->selectedProductIds = array_values(array_diff($selectedIds, $pageIds));

            return;
        }

        $this->selectedProductIds = array_values(array_unique(array_merge($selectedIds, $pageIds)));
    }

    public function editSelected()
    {
        $selectedIds = $this->normalizedSelectedIds();

        if ($selectedIds === []) {
            $this->notifyNoSelection();

            return null;
        }

        if (count($selectedIds) > 1) {
            Notification::make()
                ->title('Επίλεξε ένα προϊόν για πλήρη επεξεργασία.')
                ->warning()
                ->send();

            return null;
        }

        return redirect()->to($this->productEditUrl($selectedIds[0]));
    }

    public function addSelectedToSupplierOrder(): void
    {
        if ($this->normalizedSelectedIds() === []) {
            $this->notifyNoSelection();

            return;
        }

        Notification::make()
            ->title('Τα επιλεγμένα προϊόντα είναι έτοιμα για παραγγελία προμηθευτή.')
            ->body('Δεν υπάρχει ακόμα ξεχωριστή οντότητα παραγγελίας προμηθευτή στο project, οπότε η ενέργεια κρατήθηκε ασφαλής.')
            ->success()
            ->send();
    }

    public function deleteSelected(): void
    {
        $selectedIds = $this->normalizedSelectedIds();

        if ($selectedIds === []) {
            $this->notifyNoSelection();

            return;
        }

        Product::query()
            ->whereKey($selectedIds)
            ->delete();

        $this->selectedProductIds = [];
        $this->resetListingPage();

        Notification::make()
            ->title('Τα επιλεγμένα προϊόντα διαγράφηκαν.')
            ->success()
            ->send();
    }

    public function exportSelected(bool $availableOnly = false): ?StreamedResponse
    {
        $selectedIds = $this->normalizedSelectedIds();

        if ($selectedIds === []) {
            $this->notifyNoSelection();

            return null;
        }

        $filename = ($availableOnly ? 'available_' : 'selected_')
            . 'quick_update_products_'
            . now()->format('Ymd_His')
            . '.csv';

        return response()->streamDownload(function () use ($selectedIds, $availableOnly): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, [
                'ID',
                'Title',
                'Status',
                'Code',
                'MPN',
                'EAN',
                'Price',
                'Purchase Price',
                'Retail Price',
                'Quantity',
                'Manufacturer',
                'Supplier',
                'Category',
                'Created',
                'Updated',
                'Supplier Updated',
            ]);

            $this->buildProductQuery()
                ->whereKey($selectedIds)
                ->get()
                ->map(fn (Product $product): array => $this->mapProductForView($product))
                ->filter(fn (array $product): bool => ! $availableOnly || (float) $product['quantity_raw'] > 0)
                ->each(function (array $product) use ($handle): void {
                    fputcsv($handle, [
                        $product['display_id'],
                        $product['title'],
                        $product['active'] ? 'active' : 'inactive',
                        $product['code'],
                        $product['mpn'],
                        $product['ean'],
                        $product['price'],
                        $product['purchase_price'],
                        $product['retail_price'],
                        $product['quantity'],
                        $product['manufacturer'],
                        $product['supplier'],
                        $product['category'],
                        $product['created_at'],
                        $product['updated_at'],
                        $product['supplier_updated_at'],
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function productEditUrl(int $productId): string
    {
        return EditProduct::getUrl(['product' => $productId]) . '?return=' . rawurlencode(static::getUrl());
    }

    protected function resetListingPage(): void
    {
        $this->productPage = 1;
    }

    protected function getProductsForView(): array
    {
        if (! $this->tableExists('products')) {
            return [];
        }

        $query = $this->buildProductQuery();

        $this->totalProducts = (clone $query)->count();
        $this->perPageValue = $this->perPageLimit();
        $this->productLastPage = max(1, (int) ceil($this->totalProducts / $this->perPageValue));

        if ($this->productPage > $this->productLastPage) {
            $this->productPage = $this->productLastPage;
        }

        if ($this->productPage < 1) {
            $this->productPage = 1;
        }

        $this->productCurrentPage = $this->productPage;

        $offset = ($this->productCurrentPage - 1) * $this->perPageValue;

        $this->productFirstItem = $this->totalProducts > 0 ? $offset + 1 : 0;
        $this->productLastItem = min($this->totalProducts, $offset + $this->perPageValue);

        return (clone $query)
            ->skip($offset)
            ->take($this->perPageValue)
            ->get()
            ->map(fn (Product $product): array => $this->mapProductForView($product))
            ->all();
    }

    protected function buildProductQuery(): Builder
    {
        $query = Product::query();

        if ($this->tableExists('brands') && $this->columnExists('products', 'brand_id')) {
            $query->with('brand');
        }

        if ($this->tableExists('categories') && $this->columnExists('products', 'category_id')) {
            $query->with('category');
        }

        if ($this->tableExists('suppliers') && $this->columnExists('products', 'primary_supplier_id')) {
            $query->with('primarySupplier');
        }

        if ($this->tableExists('product_prices')) {
            $query->with('price');
        }

        if ($this->tableExists('supplier_products')) {
            $query->with(['supplierProducts' => fn ($query) => $query->with('supplier')]);
        }

        $this->applySearchFilter($query);
        $this->applyCategoryFilter($query);
        $this->applyBrandFilter($query);
        $this->applySupplierFilters($query);
        $this->applyStatusFilter($query);
        $this->applyBooleanProductFilter($query, 'price_update_enabled', $this->priceUpdateFilter);
        $this->applyBooleanProductFilter($query, 'purchase_price_update_enabled', $this->purchasePriceUpdateFilter);
        $this->applyBooleanProductFilter($query, 'status_update_enabled', $this->statusUpdateFilter);
        $this->applyPricePresenceFilter($query);
        $this->applyStockFilter($query);
        $this->applyStockTextFilter($query);
        $this->applyMiscellaneousFilter($query);

        if ($this->columnExists('products', 'updated_at')) {
            $query->orderByDesc('updated_at');
        }

        return $query->orderByDesc('id');
    }

    protected function applySearchFilter(Builder $query): void
    {
        $search = trim($this->search);

        if ($search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search): void {
            foreach (['title', 'code', 'mpn', 'ean', 'legacy_id'] as $column) {
                if ($this->columnExists('products', $column)) {
                    $query->orWhere($column, 'like', '%' . $search . '%');
                }
            }
        });
    }

    protected function applyCategoryFilter(Builder $query): void
    {
        $categoryId = $this->filterId($this->categoryFilter);

        if ($categoryId && $this->columnExists('products', 'category_id')) {
            $query->where('category_id', $categoryId);
        }
    }

    protected function applyBrandFilter(Builder $query): void
    {
        $brandId = $this->filterId($this->brandFilter);

        if ($brandId && $this->columnExists('products', 'brand_id')) {
            $query->where('brand_id', $brandId);
        }
    }

    protected function applySupplierFilters(Builder $query): void
    {
        $supplierId = $this->filterId($this->supplierFilter);
        $notSupplierId = $this->filterId($this->notSupplierFilter);
        $xmlSourceId = $this->filterId($this->xmlSourceFilter);

        if ($supplierId) {
            $query->where(function (Builder $query) use ($supplierId): void {
                if ($this->columnExists('products', 'primary_supplier_id')) {
                    $query->where('primary_supplier_id', $supplierId);
                }

                if ($this->tableExists('supplier_products')) {
                    $query->orWhereHas('supplierProducts', fn (Builder $query) => $query->where('supplier_id', $supplierId));
                }
            });
        }

        if ($notSupplierId) {
            if ($this->columnExists('products', 'primary_supplier_id')) {
                $query->where(function (Builder $query) use ($notSupplierId): void {
                    $query->whereNull('primary_supplier_id')
                        ->orWhere('primary_supplier_id', '!=', $notSupplierId);
                });
            }

            if ($this->tableExists('supplier_products')) {
                $query->whereDoesntHave('supplierProducts', fn (Builder $query) => $query->where('supplier_id', $notSupplierId));
            }
        }

        if ($xmlSourceId && $this->tableExists('supplier_products')) {
            $query->whereHas('supplierProducts', fn (Builder $query) => $query->where('supplier_id', $xmlSourceId));
        }

        if ($this->supplierLinkedFilter === 'linked') {
            $query->where(function (Builder $query): void {
                if ($this->columnExists('products', 'primary_supplier_id')) {
                    $query->whereNotNull('primary_supplier_id');
                }

                if ($this->tableExists('supplier_products')) {
                    $query->orWhereHas('supplierProducts');
                }
            });
        }

        if ($this->supplierLinkedFilter === 'unlinked') {
            if ($this->columnExists('products', 'primary_supplier_id')) {
                $query->whereNull('primary_supplier_id');
            }

            if ($this->tableExists('supplier_products')) {
                $query->whereDoesntHave('supplierProducts');
            }
        }
    }

    protected function applyStatusFilter(Builder $query): void
    {
        if (! $this->columnExists('products', 'status') && ! $this->columnExists('products', 'active')) {
            return;
        }

        if ($this->statusFilter === 'active') {
            $query->where(function (Builder $query): void {
                if ($this->columnExists('products', 'active')) {
                    $query->where('active', true);
                }

                if ($this->columnExists('products', 'status')) {
                    $query->orWhereIn('status', ['active', 'enabled', 'yes', '1', 'true']);
                }
            });
        }

        if ($this->statusFilter === 'inactive') {
            $query->where(function (Builder $query): void {
                if ($this->columnExists('products', 'active')) {
                    $query->where('active', false);
                }

                if ($this->columnExists('products', 'status')) {
                    $query->orWhereNull('status')
                        ->orWhereNotIn('status', ['active', 'enabled', 'yes', '1', 'true']);
                }
            });
        }

        if ($this->statusFilter === 'draft' && $this->columnExists('products', 'status')) {
            $query->where('status', 'draft');
        }
    }

    protected function applyBooleanProductFilter(Builder $query, string $column, string $filter): void
    {
        if (! $this->columnExists('products', $column) || ! in_array($filter, ['yes', 'no'], true)) {
            return;
        }

        $query->where($column, $filter === 'yes');
    }

    protected function applyPricePresenceFilter(Builder $query): void
    {
        if (! $this->tableExists('product_prices') || ! in_array($this->pricePresenceFilter, ['with_price', 'without_price'], true)) {
            return;
        }

        if ($this->pricePresenceFilter === 'with_price') {
            $query->whereHas('price', function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    foreach ($this->priceColumns() as $column) {
                        $query->orWhereNotNull($column);
                    }
                });
            });
        }

        if ($this->pricePresenceFilter === 'without_price') {
            $query->where(function (Builder $query): void {
                $query->whereDoesntHave('price')
                    ->orWhereHas('price', function (Builder $query): void {
                        $query->where(function (Builder $query): void {
                            foreach ($this->priceColumns() as $column) {
                                $query->whereNull($column);
                            }
                        });
                    });
            });
        }
    }

    protected function applyStockFilter(Builder $query): void
    {
        if (! in_array($this->stockFilter, ['available', 'unavailable', 'without_quantity'], true)) {
            return;
        }

        if ($this->columnExists('products', 'quantity')) {
            if ($this->stockFilter === 'available') {
                $query->where('quantity', '>', 0);
            } elseif ($this->stockFilter === 'unavailable') {
                $query->where(function (Builder $query): void {
                    $query->whereNull('quantity')->orWhere('quantity', '<=', 0);
                });
            } else {
                $query->whereNull('quantity');
            }

            return;
        }

        if (! $this->tableExists('supplier_products')) {
            return;
        }

        if ($this->stockFilter === 'available') {
            $query->whereHas('supplierProducts', fn (Builder $query) => $query->where('stock_quantity', '>', 0));
        } else {
            $query->whereDoesntHave('supplierProducts', fn (Builder $query) => $query->where('stock_quantity', '>', 0));
        }
    }

    protected function applyStockTextFilter(Builder $query): void
    {
        if ($this->stockTextFilter === 'all' || ! $this->columnExists('products', 'stock_text')) {
            return;
        }

        if ($this->stockTextFilter === '__blank') {
            $query->where(function (Builder $query): void {
                $query->whereNull('stock_text')->orWhere('stock_text', '');
            });

            return;
        }

        $query->where('stock_text', $this->stockTextFilter);
    }

    protected function applyMiscellaneousFilter(Builder $query): void
    {
        if ($this->miscellaneousFilter === 'boxnow' && $this->columnExists('products', 'boxnow_enabled')) {
            $query->where('boxnow_enabled', true);
        }

        if ($this->miscellaneousFilter === 'article11' && $this->columnExists('products', 'article_11_enabled')) {
            $query->where('article_11_enabled', true);
        }

        if ($this->miscellaneousFilter === 'with_image') {
            $query->where(function (Builder $query): void {
                foreach (['image_path', 'main_image'] as $column) {
                    if ($this->columnExists('products', $column)) {
                        $query->orWhereNotNull($column);
                    }
                }
            });
        }

        if ($this->miscellaneousFilter === 'without_image') {
            $query->where(function (Builder $query): void {
                foreach (['image_path', 'main_image'] as $column) {
                    if ($this->columnExists('products', $column)) {
                        $query->where(function (Builder $query) use ($column): void {
                            $query->whereNull($column)->orWhere($column, '');
                        });
                    }
                }
            });
        }
    }

    protected function mapProductForView(Product $product): array
    {
        $supplierProducts = $product->relationLoaded('supplierProducts')
            ? $product->supplierProducts
            : collect();

        $supplierProduct = $supplierProducts->sortByDesc(
            fn ($supplierProduct) => $supplierProduct->last_seen_at ?? $supplierProduct->updated_at,
        )->first();

        $quantity = $this->productQuantity($product, $supplierProduct);

        return [
            'id' => $product->id,
            'display_id' => $this->productDisplayId($product),
            'image' => $this->productImageForView($product),
            'title' => $this->getProductTitle($product),
            'active' => $this->isProductActive($product),
            'code' => $this->getModelValue($product, 'code', '-'),
            'mpn' => $this->getModelValue($product, 'mpn', $supplierProduct?->mpn ?: ''),
            'ean' => $this->getModelValue($product, 'ean', $supplierProduct?->ean ?: ''),
            'price' => $this->getProductPrice($product),
            'purchase_price' => $this->getProductPriceColumn($product, 'cost_ex_vat'),
            'retail_price' => $this->getProductPriceColumn($product, 'retail_inc_vat'),
            'quantity' => $this->formatQuantity($quantity),
            'quantity_raw' => $quantity,
            'manufacturer' => $this->getBrandName($product, $supplierProduct),
            'supplier' => $this->getSupplierName($product, $supplierProduct),
            'category' => $this->getProductCategoryName($product),
            'created_at' => $this->formatDateValue($product->created_at ?? null),
            'updated_at' => $this->formatDateValue($product->updated_at ?? null),
            'supplier_updated_at' => $this->formatDateValue($supplierProduct?->last_seen_at ?? $supplierProduct?->updated_at),
        ];
    }

    protected function getCurrentPageProductIds(): array
    {
        return array_map(
            fn (array $product): int => (int) $product['id'],
            $this->getProductsForView(),
        );
    }

    protected function normalizedSelectedIds(): array
    {
        return array_values(array_unique(array_filter(
            array_map('intval', $this->selectedProductIds),
            fn (int $id): bool => $id > 0,
        )));
    }

    protected function notifyNoSelection(): void
    {
        Notification::make()
            ->title('Δεν έχεις επιλέξει προϊόντα.')
            ->warning()
            ->send();
    }

    protected function perPageLimit(): int
    {
        return in_array($this->perPage, ['25', '50', '100', '300'], true)
            ? (int) $this->perPage
            : 50;
    }

    protected function filterId(string $value): ?int
    {
        return ctype_digit($value) && (int) $value > 0 ? (int) $value : null;
    }

    protected function priceColumns(): array
    {
        return array_values(array_filter(
            ['retail_inc_vat', 'discount_inc_vat', 'skroutz_inc_vat', 'wholesale_inc_vat', 'cost_ex_vat'],
            fn (string $column): bool => $this->columnExists('product_prices', $column),
        ));
    }

    protected function getCategoryOptions(): array
    {
        if (! $this->tableExists('categories')) {
            return [];
        }

        return Category::query()
            ->orderBy($this->columnExists('categories', 'full_path') ? 'full_path' : 'name')
            ->limit(500)
            ->get()
            ->map(fn (Category $category): array => [
                'id' => (string) $category->id,
                'name' => $this->getCategoryLabel($category),
            ])
            ->all();
    }

    protected function getBrandOptions(): array
    {
        if (! $this->tableExists('brands')) {
            return [];
        }

        return Brand::query()
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name'])
            ->map(fn (Brand $brand): array => [
                'id' => (string) $brand->id,
                'name' => (string) $brand->name,
            ])
            ->all();
    }

    protected function getSupplierOptions(): array
    {
        if (! $this->tableExists('suppliers')) {
            return [];
        }

        return Supplier::query()
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name'])
            ->map(fn (Supplier $supplier): array => [
                'id' => (string) $supplier->id,
                'name' => (string) $supplier->name,
            ])
            ->all();
    }

    protected function getStockTextOptions(): array
    {
        if (! $this->columnExists('products', 'stock_text')) {
            return [];
        }

        return Product::query()
            ->select('stock_text')
            ->whereNotNull('stock_text')
            ->where('stock_text', '!=', '')
            ->distinct()
            ->orderBy('stock_text')
            ->limit(100)
            ->pluck('stock_text')
            ->map(fn ($value): string => (string) $value)
            ->all();
    }

    protected function productDisplayId(Product $product): string
    {
        $legacyId = $this->getModelValue($product, 'legacy_id', null);

        return $legacyId ? (string) $legacyId : (string) $product->id;
    }

    protected function getCategoryLabel(Category $category): string
    {
        if ($this->columnExists('categories', 'full_path') && ! empty($category->full_path)) {
            return str_replace('>', ' / ', (string) $category->full_path);
        }

        return (string) $this->getModelValue($category, 'name', 'Κατηγορία #' . $category->id);
    }

    protected function productImageForView(Product $product): string
    {
        foreach (['image_path', 'main_image', 'image', 'image_url', 'products_image', 'photo', 'thumbnail'] as $column) {
            if ($this->columnExists('products', $column) && ! empty($product->{$column})) {
                return $this->normalizeProductImageUrl((string) $product->{$column});
            }
        }

        $supplierProducts = $product->relationLoaded('supplierProducts')
            ? $product->supplierProducts
            : collect();

        foreach ($supplierProducts as $supplierProduct) {
            $images = $supplierProduct->images;

            if (is_array($images) && isset($images[0]) && is_string($images[0])) {
                return $this->normalizeProductImageUrl($images[0]);
            }
        }

        return '';
    }

    protected function normalizeProductImageUrl(string $image): string
    {
        $image = trim($image);

        if ($image === '') {
            return '';
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        if (str_starts_with($image, '//')) {
            return 'https:' . $image;
        }

        if (str_starts_with($image, '/')) {
            return $image;
        }

        if (str_starts_with($image, 'storage/')) {
            return asset($image);
        }

        if (str_starts_with($image, 'images/') || str_starts_with($image, 'uploads/') || str_starts_with($image, 'product_images/')) {
            return asset($image);
        }

        return asset('storage/' . ltrim($image, '/'));
    }

    protected function getProductTitle(Product $product): string
    {
        return (string) $this->getModelValue($product, 'title', 'Προϊόν #' . $product->id);
    }

    protected function isProductActive(Product $product): bool
    {
        if ($this->columnExists('products', 'active')) {
            return $this->booleanValue($product->active);
        }

        if ($this->columnExists('products', 'status')) {
            return $this->booleanValue($product->status);
        }

        return false;
    }

    protected function productQuantity(Product $product, $supplierProduct): float
    {
        foreach (['quantity', 'stock_quantity'] as $column) {
            if ($this->columnExists('products', $column) && $product->{$column} !== null) {
                return (float) $product->{$column};
            }
        }

        return (float) ($supplierProduct?->stock_quantity ?? 0);
    }

    protected function getBrandName(Product $product, $supplierProduct): string
    {
        if ($this->columnExists('products', 'brand_name') && ! empty($product->brand_name)) {
            return (string) $product->brand_name;
        }

        if ($this->columnExists('products', 'manufacturer') && ! empty($product->manufacturer)) {
            return (string) $product->manufacturer;
        }

        if ($product->relationLoaded('brand') && $product->brand) {
            return (string) $product->brand->name;
        }

        return $supplierProduct?->brand_name ?: '-';
    }

    protected function getSupplierName(Product $product, $supplierProduct): string
    {
        if ($this->columnExists('products', 'supplier_name') && ! empty($product->supplier_name)) {
            return (string) $product->supplier_name;
        }

        if ($product->relationLoaded('primarySupplier') && $product->primarySupplier) {
            return (string) $product->primarySupplier->name;
        }

        if ($supplierProduct?->supplier) {
            return (string) $supplierProduct->supplier->name;
        }

        return '-';
    }

    protected function getProductCategoryName(Product $product): string
    {
        if ($this->columnExists('products', 'category_name') && ! empty($product->category_name)) {
            return (string) $product->category_name;
        }

        if ($product->relationLoaded('category') && $product->category) {
            return $this->getCategoryLabel($product->category);
        }

        return '-';
    }

    protected function getProductPrice(Product $product): string
    {
        if ($product->relationLoaded('price') && $product->price) {
            foreach ($this->priceColumns() as $column) {
                if ($product->price->{$column} !== null) {
                    return $this->formatMoney($product->price->{$column});
                }
            }
        }

        foreach (['price', 'retail_inc_vat', 'selling_price', 'price_inc_vat'] as $column) {
            if ($this->columnExists('products', $column) && $product->{$column} !== null) {
                return $this->formatMoney($product->{$column});
            }
        }

        return '-';
    }

    protected function getProductPriceColumn(Product $product, string $column): string
    {
        if (
            $product->relationLoaded('price')
            && $product->price
            && $this->columnExists('product_prices', $column)
            && $product->price->{$column} !== null
        ) {
            return $this->formatMoney($product->price->{$column});
        }

        if ($this->columnExists('products', $column) && $product->{$column} !== null) {
            return $this->formatMoney($product->{$column});
        }

        return '-';
    }

    protected function booleanValue($value): bool
    {
        return in_array($value, [true, 1, '1', 'true', 'yes', 'on', 'active', 'enabled'], true);
    }

    protected function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    protected function columnExists(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    protected function getModelValue($model, string $column, $default = null)
    {
        if (! $model || ! array_key_exists($column, $model->getAttributes())) {
            return $default;
        }

        return $model->{$column} ?? $default;
    }

    protected function formatMoney($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return '€' . number_format((float) $value, 2, ',', '.');
    }

    protected function formatQuantity(float $value): string
    {
        if (floor($value) === $value) {
            return (string) (int) $value;
        }

        return number_format($value, 2, ',', '.');
    }

    protected function formatDateValue($value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return $value->format('d/m/Y H:i:s');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    protected function exportStatusForXlsx(object $product): string
    {
        if (property_exists($product, 'status') && trim((string) ($product->status ?? '')) !== '') {
            return (string) $product->status;
        }

        if (property_exists($product, 'active')) {
            return (int) $product->active === 1 ? 'active' : 'inactive';
        }

        return '';
    }

    protected function exportPriceForXlsx(object $product): int|float|string
    {
        foreach (['retail_inc_vat', 'price', 'selling_price', 'sale_price'] as $column) {
            if (property_exists($product, $column) && $product->{$column} !== null && $product->{$column} !== '') {
                return is_numeric($product->{$column}) ? (float) $product->{$column} : (string) $product->{$column};
            }
        }

        return '';
    }

    protected function exportQuantityForXlsx(object $product): int|float|string
    {
        foreach (['quantity', 'stock_quantity', 'stock'] as $column) {
            if (property_exists($product, $column) && $product->{$column} !== null && $product->{$column} !== '') {
                return is_numeric($product->{$column}) ? (float) $product->{$column} : (string) $product->{$column};
            }
        }

        return '';
    }

    protected function exportManufacturerForXlsx(object $product): string
    {
        foreach (['brand_name', 'manufacturer', 'brand'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    protected function exportSupplierForXlsx(object $product): string
    {
        foreach (['supplier_name', 'supplier'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    protected function exportCategoryForXlsx(object $product): string
    {
        if (property_exists($product, 'category_id') && $product->category_id) {
            return $this->categoryPathForExport((int) $product->category_id, (string) ($product->category_name ?? ''));
        }

        foreach (['category_path', 'category', 'category_name'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    public function exportScope()
    {
        $filename = 'products_export_all_' . now()->format('Ymd_His') . '.xlsx';
        $path = storage_path('app/exports/' . $filename);

        app(CatalogProductsExcelExporter::class)->export($path, [
            'categoryFilter' => $this->categoryFilter ?? 'all',
            'search' => $this->search ?? '',
            'brandFilter' => $this->brandFilter ?? 'all',
            'supplierFilter' => $this->supplierFilter ?? 'all',
            'notSupplierFilter' => $this->notSupplierFilter ?? 'all',
            'statusFilter' => $this->statusFilter ?? 'all',
            'includeCategoryDescendants' => true,
        ]);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }



    protected function createProductsExportXlsx(string $path): void
    {
        $headers = [
            'ID',
            'Κωδικός',
            'MPN',
            'EAN',
            'Τίτλος',
            'Κατασκευαστής',
            'Προμηθευτής',
            'Κατηγορία',
            'ΦΠΑ',
            'Τιμή αγοράς (ΧΩΡΙΣ ΦΠΑ)',
            'Τιμή πώλησης (ΜΕ ΦΠΑ)',
            'Τιμή έκπτωσης (ΜΕ ΦΠΑ)',
            'Τιμή Skroutz (ΜΕ ΦΠΑ)',
            'Χονδρική',
            'Sliver',
            'Gold',
            'Βάρος (kg)',
            'Διαστάσεις Πλ x Y x Πάχ(εκ)',
            'Meta desc',
            'Meta keywords',
            'Δ. αρχ. 05-05-2026 09:07',
        ];

        $sharedStrings = [];
        $sharedStringIndex = [];
        $sheetRows = [];

        $sheetRows[] = $this->xlsxRow(1, $headers, $sharedStrings, $sharedStringIndex);

        $rowNumber = 2;
        $query = $this->buildProductQueryForExport();

        $query->chunk(1000, function ($products) use (&$sheetRows, &$sharedStrings, &$sharedStringIndex, &$rowNumber): void {
            foreach ($products as $product) {
                $row = [
                    $product->legacy_id ?? $product->id,
                    $product->code ?? '',
                    $product->mpn ?? '',
                    $product->ean ?? '',
                    $product->title ?? '',
                    $this->exportManufacturerForExcel($product),
                    $this->exportSupplierForExcel($product),
                    $this->exportCategoryForExcel($product),
                    $this->exportVatForExcel($product),
                    $this->exportValueForExcel($product, ['cost_ex_vat']),
                    $this->exportValueForExcel($product, ['retail_inc_vat']),
                    $this->exportValueForExcel($product, ['discount_inc_vat']),
                    $this->exportValueForExcel($product, ['skroutz_inc_vat']),
                    $this->exportValueForExcel($product, ['wholesale_inc_vat']),
                    $this->exportValueForExcel($product, ['silver_inc_vat']),
                    $this->exportValueForExcel($product, ['gold_inc_vat']),
                    $this->exportValueForExcel($product, ['weight_kg', 'weight']),
                    $this->exportValueForExcel($product, ['dimensions']),
                    $this->exportValueForExcel($product, ['meta_description']),
                    $this->exportValueForExcel($product, ['meta_keywords']),
                    '',
                ];

                $sheetRows[] = $this->xlsxRow($rowNumber, $row, $sharedStrings, $sharedStringIndex);
                $rowNumber++;
            }
        });

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create export file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->xlsxContentTypes());
        $zip->addFromString('_rels/.rels', $this->xlsxRootRels());
        $zip->addFromString('xl/workbook.xml', $this->xlsxWorkbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->xlsxWorkbookRels());
        $zip->addFromString('xl/styles.xml', $this->xlsxStyles());
        $zip->addFromString('xl/sharedStrings.xml', $this->xlsxSharedStrings($sharedStrings));
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->xlsxSheet(implode('', $sheetRows)));
        $zip->addFromString('docProps/core.xml', $this->xlsxCore());
        $zip->addFromString('docProps/app.xml', $this->xlsxApp());

        $zip->close();
    }

    protected function buildProductQueryForExport()
    {
        $query = DB::table('products');

        if (Schema::hasTable('product_prices')) {
            $query->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id');
        }

        if (Schema::hasTable('brands') && Schema::hasColumn('products', 'brand_id')) {
            $query->leftJoin('brands', 'brands.id', '=', 'products.brand_id');
        }

        if (Schema::hasTable('suppliers') && Schema::hasColumn('products', 'supplier_id')) {
            $query->leftJoin('suppliers', 'suppliers.id', '=', 'products.supplier_id');
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('products', 'category_id')) {
            $query->leftJoin('categories', 'categories.id', '=', 'products.category_id');
        }

        $select = ['products.id'];

        foreach ([
            'legacy_id',
            'code',
            'mpn',
            'ean',
            'title',
            'manufacturer',
            'brand',
            'supplier',
            'category',
            'category_path',
            'vat',
            'vat_rate',
            'weight',
            'weight_kg',
            'dimensions',
            'meta_description',
            'meta_keywords',
            'status',
            'active',
            'category_id',
            'brand_id',
            'supplier_id',
        ] as $column) {
            if (Schema::hasColumn('products', $column)) {
                $select[] = 'products.' . $column;
            }
        }

        if (Schema::hasTable('product_prices')) {
            foreach ([
                'cost_ex_vat',
                'retail_inc_vat',
                'discount_inc_vat',
                'skroutz_inc_vat',
                'wholesale_inc_vat',
                'silver_inc_vat',
                'gold_inc_vat',
            ] as $column) {
                if (Schema::hasColumn('product_prices', $column)) {
                    $select[] = 'product_prices.' . $column;
                }
            }
        }

        $select[] = Schema::hasTable('brands')
            ? DB::raw('brands.name as brand_name')
            : DB::raw("'' as brand_name");

        $select[] = Schema::hasTable('suppliers')
            ? DB::raw('suppliers.name as supplier_name')
            : DB::raw("'' as supplier_name");

        $select[] = Schema::hasTable('categories')
            ? DB::raw('categories.name as category_name')
            : DB::raw("'' as category_name");

        $query->select($select);

        if (property_exists($this, 'search') && trim((string) $this->search) !== '') {
            $search = '%' . trim((string) $this->search) . '%';

            $query->where(function ($query) use ($search): void {
                foreach (['title', 'code', 'mpn', 'ean'] as $column) {
                    if (Schema::hasColumn('products', $column)) {
                        $query->orWhere('products.' . $column, 'like', $search);
                    }
                }
            });
        }

        if (
            property_exists($this, 'categoryFilter')
            && ! in_array((string) $this->categoryFilter, ['all', '', '0'], true)
            && Schema::hasColumn('products', 'category_id')
        ) {
            $query->where('products.category_id', (int) $this->categoryFilter);
        }

        if (
            property_exists($this, 'brandFilter')
            && ! in_array((string) $this->brandFilter, ['all', '', '0'], true)
            && Schema::hasColumn('products', 'brand_id')
        ) {
            $query->where('products.brand_id', (int) $this->brandFilter);
        }

        if (
            property_exists($this, 'supplierFilter')
            && ! in_array((string) $this->supplierFilter, ['all', '', '0'], true)
            && Schema::hasColumn('products', 'supplier_id')
        ) {
            $query->where('products.supplier_id', (int) $this->supplierFilter);
        }

        if (
            property_exists($this, 'notSupplierFilter')
            && ! in_array((string) $this->notSupplierFilter, ['all', '', '0'], true)
            && Schema::hasColumn('products', 'supplier_id')
        ) {
            $query->where('products.supplier_id', '!=', (int) $this->notSupplierFilter);
        }

        if (property_exists($this, 'statusFilter') && ! in_array((string) $this->statusFilter, ['all', ''], true)) {
            if (Schema::hasColumn('products', 'status')) {
                $query->where('products.status', $this->statusFilter);
            } elseif (Schema::hasColumn('products', 'active')) {
                $query->where('products.active', $this->statusFilter === 'active' ? 1 : 0);
            }
        }

        $query->orderBy('products.id');

        return $query;
    }

    protected function exportManufacturerForExcel(object $product): string
    {
        foreach (['brand_name', 'manufacturer', 'brand'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    protected function exportSupplierForExcel(object $product): string
    {
        foreach (['supplier_name', 'supplier'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    protected function exportCategoryForExcel(object $product): string
    {
        if (property_exists($product, 'category_id') && $product->category_id) {
            return $this->categoryPathForExport((int) $product->category_id, (string) ($product->category_name ?? ''));
        }

        foreach (['category_path', 'category', 'category_name'] as $column) {
            if (property_exists($product, $column) && trim((string) ($product->{$column} ?? '')) !== '') {
                return (string) $product->{$column};
            }
        }

        return '';
    }

    protected function exportVatForExcel(object $product): int|float|string
    {
        foreach (['vat', 'vat_rate'] as $column) {
            if (property_exists($product, $column) && $product->{$column} !== null && $product->{$column} !== '') {
                return is_numeric($product->{$column}) ? (float) $product->{$column} : (string) $product->{$column};
            }
        }

        return 19;
    }

    protected function exportValueForExcel(object $product, array $columns): int|float|string
    {
        foreach ($columns as $column) {
            if (property_exists($product, $column) && $product->{$column} !== null && $product->{$column} !== '') {
                return is_numeric($product->{$column}) ? (float) $product->{$column} : (string) $product->{$column};
            }
        }

        return '';
    }

    protected function categoryPathForExport(int $categoryId, string $fallback = ''): string
    {
        static $cache = [];

        if (isset($cache[$categoryId])) {
            return $cache[$categoryId];
        }

        if (! Schema::hasTable('categories')) {
            return $fallback;
        }

        $names = [];
        $currentId = $categoryId;
        $guard = 0;

        while ($currentId && $guard < 50) {
            $category = DB::table('categories')
                ->where('id', $currentId)
                ->first();

            if (! $category) {
                break;
            }

            array_unshift($names, (string) ($category->name ?? ''));

            $currentId = property_exists($category, 'parent_id') ? (int) ($category->parent_id ?? 0) : 0;
            $guard++;
        }

        $path = trim(implode(' > ', array_filter($names)));

        if ($path === '') {
            $path = $fallback;
        }

        $cache[$categoryId] = $path;

        return $path;
    }

    protected function xlsxRow(int $rowNumber, array $values, array &$sharedStrings, array &$sharedStringIndex): string
    {
        $cells = '';
        $columnIndex = 1;

        foreach ($values as $value) {
            $cell = $this->xlsxColumnName($columnIndex) . $rowNumber;

            if (is_int($value) || is_float($value)) {
                $cells .= '<c r="' . $cell . '"><v>' . $value . '</v></c>';
            } else {
                $value = (string) ($value ?? '');

                if (! array_key_exists($value, $sharedStringIndex)) {
                    $sharedStringIndex[$value] = count($sharedStrings);
                    $sharedStrings[] = $value;
                }

                $cells .= '<c r="' . $cell . '" t="s"><v>' . $sharedStringIndex[$value] . '</v></c>';
            }

            $columnIndex++;
        }

        return '<row r="' . $rowNumber . '">' . $cells . '</row>';
    }

    protected function xlsxColumnName(int|string $index): string
    {
        $index = (int) $index;
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    protected function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    protected function xlsxSharedStrings(array $strings): string
    {
        $items = '';

        foreach ($strings as $string) {
            $items .= '<si><t xml:space="preserve">' . $this->xmlEscape((string) $string) . '</t></si>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($strings) . '" uniqueCount="' . count($strings) . '">'
            . $items
            . '</sst>';
    }

    protected function xlsxSheet(string $rows): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<sheetData>' . $rows . '</sheetData>'
            . '</worksheet>';
    }

    protected function xlsxContentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '</Types>';
    }

    protected function xlsxRootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    protected function xlsxWorkbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Products" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    protected function xlsxWorkbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '</Relationships>';
    }

    protected function xlsxStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '</styleSheet>';
    }

    protected function xlsxCore(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:creator>E-commerce Admin</dc:creator>'
            . '<cp:lastModifiedBy>E-commerce Admin</cp:lastModifiedBy>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . now()->toISOString() . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . now()->toISOString() . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    protected function xlsxApp(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            . '<Application>E-commerce Admin</Application>'
            . '</Properties>';
    }


}
