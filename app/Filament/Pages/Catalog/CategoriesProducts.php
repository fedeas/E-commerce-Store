<?php

namespace App\Filament\Pages\Catalog;


use App\Support\CatalogProductsExcelExporter;use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\Supplier;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class CategoriesProducts extends Page
{

    public function handleCategoryRowClick(int $categoryId): void
    {
        if ($categoryId <= 0) {
            return;
        }

        if ($this->categorySearchTerm() !== '') {
            $this->selectCategory($categoryId);
            return;
        }

        if ($this->selectedCatalogType === 'category' && (int) $this->selectedCatalogId === $categoryId) {
            $this->selectCategory($categoryId);
            return;
        }

        $this->selectCatalogItem('category', $categoryId);
    }


    public function toggleCatalogStatus(string $type, int $id, bool $value): void
    {
        if ($id <= 0) {
            return;
        }

        $table = match ($type) {
            'category' => 'categories',
            'product' => 'products',
            default => null,
        };

        if (! $table || ! Schema::hasTable($table)) {
            return;
        }

        $updates = [];

        if (Schema::hasColumn($table, 'active')) {
            $updates['active'] = $value ? 1 : 0;
        }

        if (Schema::hasColumn($table, 'status')) {
            $updates['status'] = $this->catalogStatusDatabaseValue($table, 'status', $value);
        }

        if ($updates === []) {
            return;
        }

        DB::table($table)
            ->where('id', $id)
            ->update($updates);
    }

    private function catalogStatusDatabaseValue(string $table, string $column, bool $value): mixed
    {
        try {
            $type = Schema::getColumnType($table, $column);
        } catch (\Throwable $e) {
            $type = 'string';
        }

        if (in_array($type, ['boolean', 'bool', 'tinyint', 'smallint', 'integer', 'bigint'], true)) {
            return $value ? 1 : 0;
        }

        return $value ? 'active' : 'inactive';
    }


    public ?string $selectedCatalogType = null;

    public ?int $selectedCatalogId = null;

    public function selectCatalogItem(string $type, int $id): void
    {
        if (! in_array($type, ['category', 'product'], true)) {
            return;
        }

        if ($id <= 0) {
            return;
        }

        $this->selectedCatalogType = $type;
        $this->selectedCatalogId = $id;
    }

    public function clearCatalogSelection(): void
    {
        $this->selectedCatalogType = null;
        $this->selectedCatalogId = null;
    }

    use WithFileUploads;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-folder-open';

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $title = 'Κατηγορίες / Προϊόντα';

    protected static ?string $navigationLabel = 'Κατηγορίες / Προϊόντα';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.catalog.categories-products';

    
    protected array $queryString = [
        'selectedCategoryId' => ['except' => null, 'as' => 'cat'],
        'selectedCatalogType' => ['except' => null, 'as' => 'type'],
        'selectedCatalogId' => ['except' => null, 'as' => 'item'],
        'productPage' => ['except' => 1, 'as' => 'page'],
        'productPerPage' => ['except' => '50', 'as' => 'perPage'],
        'productSearch' => ['except' => '', 'as' => 'q'],
        'statusFilter' => ['except' => 'all', 'as' => 'status'],
    ];
public ?int $selectedCategoryId = null;
    public string $productPerPage = '50';

    public int $productPage = 1;

    public int $totalProducts = 0;

    public int $productFirstItem = 0;

    public int $productLastItem = 0;

    public int $productLastPage = 1;

    public int $productCurrentPage = 1;

    public int $productPerPageValue = 50;


    public string $categorySearch = '';

    public string $productSearch = '';

    public string $statusFilter = 'all';

    public bool $showCategoryModal = false;

    public bool $showProductModal = false;

    public ?int $categoryParentId = null;

    public string $categoryName = '';

    public bool $categoryActive = true;

    public ?int $productCategoryId = null;

    public string $productTitle = '';

    public string $productCode = '';

    public string $productMpn = '';

    public string $productEan = '';

    public string $productPrice = '';

    public string $productStatus = 'active';

    public $priceCsv = null;

    protected function getViewData(): array
    {
        $categories = $this->getCategoriesForView();
        $products = $this->getProductsForView();

        return [
            'categories' => $categories,
            'products' => $products,
            'totalProducts' => $this->totalProducts,
            'productFirstItem' => $this->productFirstItem,
            'productLastItem' => $this->productLastItem,
            'productLastPage' => $this->productLastPage,
            'productCurrentPage' => $this->productCurrentPage,
            'productPerPageValue' => $this->productPerPageValue,
            'selectedCategory' => $this->getSelectedCategoryForView(),
            'currentPath' => $this->getCurrentPathForView(),
            'categoriesCount' => count($categories),
            'productsCount' => count($products),
        ];
    }

    public function selectCategory(int $categoryId): void
    {
        if (! $this->tableExists('categories')) {
            return;
        }

        $exists = Category::query()
            ->whereKey($categoryId)
            ->exists();

        if (! $exists) {
            return;
        }

        $this->selectedCategoryId = $categoryId;

        $this->productPage = 1;
        $this->categorySearch = '';
        $this->productSearch = '';
    }

    public function goToParent(): void
    {
        $this->productPage = 1;
        if (! $this->selectedCategoryId || ! $this->tableExists('categories')) {
            $this->selectedCategoryId = null;

            return;
        }

        $category = Category::query()->find($this->selectedCategoryId);

        if (! $category) {
            $this->selectedCategoryId = null;

            return;
        }

        if ($this->columnExists('categories', 'parent_id')) {
            $parentId = $category->parent_id;

            $this->selectedCategoryId = ! empty($parentId) ? (int) $parentId : null;

            return;
        }

        $this->selectedCategoryId = null;
    }

    public function openCategoryModal(): void
    {
        $this->categoryParentId = $this->selectedCategoryId;
        $this->categoryName = '';
        $this->categoryActive = true;
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->categoryName = '';
        $this->categoryActive = true;
    }

    public function openProductModal(): void
    {
        $this->productCategoryId = $this->selectedCategoryId;
        $this->productTitle = '';
        $this->productCode = '';
        $this->productMpn = '';
        $this->productEan = '';
        $this->productPrice = '';
        $this->productStatus = 'active';
        $this->showProductModal = true;
    }

    public function closeProductModal(): void
    {
        $this->showProductModal = false;
        $this->productTitle = '';
        $this->productCode = '';
        $this->productMpn = '';
        $this->productEan = '';
        $this->productPrice = '';
        $this->productStatus = 'active';
    }

    public function createCategory(): void
    {
        if (! $this->tableExists('categories')) {
            return;
        }

        $name = trim($this->categoryName);

        if ($name === '') {
            Notification::make()
                ->title('Συμπλήρωσε όνομα κατηγορίας.')
                ->danger()
                ->send();

            return;
        }

        $data = [];

        if ($this->columnExists('categories', 'name')) {
            $data['name'] = $name;
        }

        if ($this->columnExists('categories', 'parent_id')) {
            $data['parent_id'] = $this->categoryParentId ?: null;
        }

        if ($this->columnExists('categories', 'slug')) {
            $data['slug'] = $this->uniqueSlug('categories', $name);
        }

        if ($this->columnExists('categories', 'full_path')) {
            $parentPath = $this->categoryParentId ? $this->getCategoryPathById($this->categoryParentId) : '';
            $data['full_path'] = trim($parentPath . ' > ' . $name, ' >');
        }

        if ($this->columnExists('categories', 'level')) {
            $data['level'] = $this->categoryParentId ? $this->getCategoryLevel($this->categoryParentId) + 1 : 0;
        }

        if ($this->columnExists('categories', 'sort_order')) {
            $data['sort_order'] = $this->nextCategorySortOrder($this->categoryParentId);
        }

        if ($this->columnExists('categories', 'active')) {
            $data['active'] = $this->categoryActive;
        } elseif ($this->columnExists('categories', 'status')) {
            $data['status'] = $this->categoryActive ? 'active' : 'inactive';
        }

        Category::query()->create($data);

        $this->closeCategoryModal();

        Notification::make()
            ->title('Η κατηγορία δημιουργήθηκε.')
            ->success()
            ->send();
    }

    public function createProduct(): void
    {
        if (! $this->tableExists('products')) {
            return;
        }

        $title = trim($this->productTitle);

        if ($title === '') {
            Notification::make()
                ->title('Συμπλήρωσε τίτλο προϊόντος.')
                ->danger()
                ->send();

            return;
        }

        $data = [];

        if ($this->columnExists('products', 'title')) {
            $data['title'] = $title;
        }

        if ($this->columnExists('products', 'slug')) {
            $data['slug'] = $this->uniqueSlug('products', $title);
        }

        if ($this->columnExists('products', 'code')) {
            $data['code'] = $this->emptyToNull($this->productCode);
        }

        if ($this->columnExists('products', 'mpn')) {
            $data['mpn'] = $this->emptyToNull($this->productMpn);
        }

        if ($this->columnExists('products', 'ean')) {
            $data['ean'] = $this->emptyToNull($this->productEan);
        }

        if ($this->columnExists('products', 'category_id')) {
            $data['category_id'] = $this->productCategoryId ?: $this->selectedCategoryId;
        }

        if ($this->columnExists('products', 'status')) {
            $data['status'] = $this->productStatus;
        } elseif ($this->columnExists('products', 'active')) {
            $data['active'] = $this->productStatus === 'active';
        }

        $product = Product::query()->create($data);

        if ($this->tableExists('product_prices') && $this->columnExists('product_prices', 'product_id')) {
            $priceData = [
                'product_id' => $product->id,
            ];

            $price = $this->normalizeDecimal($this->productPrice);

            foreach (['retail_inc_vat', 'discount_inc_vat', 'skroutz_inc_vat', 'wholesale_inc_vat', 'cost_ex_vat'] as $column) {
                if ($this->columnExists('product_prices', $column)) {
                    $priceData[$column] = $price;
                    break;
                }
            }

            ProductPrice::query()->create($priceData);
        }

        $this->closeProductModal();

        Notification::make()
            ->title('Το προϊόν δημιουργήθηκε.')
            ->success()
            ->send();
    }

    public function exportScope()
    {
        $filename = 'products_export_all_' . now()->format('Ymd_His') . '.xlsx';
        $path = storage_path('app/exports/' . $filename);

        app(CatalogProductsExcelExporter::class)->export($path, [
            'selectedCategoryId' => $this->selectedCategoryId ?? null,
            'search' => $this->catalogSearchTerm(),
            'statusFilter' => $this->statusFilter ?? 'all',
            'includeCategoryDescendants' => true,
        ]);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }


    public function getCategoriesForView(): array
    {
        if (! $this->tableExists('categories')) {
            return [];
        }

        if ($this->productSearchTerm() !== '') {
            return [];
        }

        $query = Category::query();
        $categorySearch = $this->categorySearchTerm();

        if ($categorySearch !== '') {
            $query->where(function (Builder $query) use ($categorySearch): void {
                if ($this->columnExists('categories', 'name')) {
                    $query->orWhere('name', 'like', '%' . $categorySearch . '%');
                }

                if ($this->columnExists('categories', 'full_path')) {
                    $query->orWhere('full_path', 'like', '%' . $categorySearch . '%');
                }
            });
        } elseif ($this->columnExists('categories', 'parent_id')) {
            if ($this->selectedCategoryId) {
                $query->where('parent_id', $this->selectedCategoryId);
            } else {
                $query->where(function (Builder $query): void {
                    $query->whereNull('parent_id')->orWhere('parent_id', 0);
                });
            }
        }

        if ($this->columnExists('categories', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        if ($this->columnExists('categories', 'name')) {
            $query->orderBy('name');
        }

        return $query
            ->limit(100)
            ->get()
            ->map(fn (Category $category): array => $this->mapCategoryForView($category))
            ->all();
    }
    public function updatedProductPerPage(): void
    {
        if (! in_array($this->productPerPage, ['25', '50', '100', '300'], true)) {
            $this->productPerPage = '50';
        }

        $this->productPage = 1;
    }

    public function updatedProductSearch(): void
    {
        $this->productPage = 1;
    }

    public function updatedCategorySearch(): void
    {
        $this->productPage = 1;
    }

    public function updatedStatusFilter(): void
    {
        $this->productPage = 1;
    }

    public function gotoProductPage(int $page): void
    {
        if ($page < 1) {
            $page = 1;
        }

        $this->productPage = $page;
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

    private function productPerPageLimit(): int
    {
        return in_array($this->productPerPage, ['25', '50', '100', '300'], true)
            ? (int) $this->productPerPage
            : 50;
    }
    public function productEditUrl(int $productId): string
    {
        $returnUrl = $this->catalogReturnUrl();

        return EditProduct::getUrl(['product' => $productId]) . '?return=' . rawurlencode($returnUrl);
    }

    protected function catalogReturnUrl(): string
    {
        $query = [];

        if ($this->selectedCategoryId) {
            $query['cat'] = $this->selectedCategoryId;
        }

        if (property_exists($this, 'selectedCatalogType') && $this->selectedCatalogType) {
            $query['type'] = $this->selectedCatalogType;
        }

        if (property_exists($this, 'selectedCatalogId') && $this->selectedCatalogId) {
            $query['item'] = $this->selectedCatalogId;
        }

        if (property_exists($this, 'productPage') && $this->productPage > 1) {
            $query['page'] = $this->productPage;
        }

        if (property_exists($this, 'productPerPage') && $this->productPerPage !== '50') {
            $query['perPage'] = $this->productPerPage;
        }

        if (property_exists($this, 'productSearch') && trim((string) $this->productSearch) !== '') {
            $query['q'] = trim((string) $this->productSearch);
        }

        if (property_exists($this, 'statusFilter') && $this->statusFilter !== 'all') {
            $query['status'] = $this->statusFilter;
        }

        return static::getUrl() . ($query !== [] ? '?' . http_build_query($query) : '');
    }

    protected function productImageForView(object $product): string
    {
        foreach (['image_path', 'main_image', 'image', 'image_url', 'products_image', 'photo', 'thumbnail'] as $column) {
            try {
                $value = $product->{$column} ?? null;
            } catch (\Throwable $e) {
                $value = null;
            }

            if ($value !== null && trim((string) $value) !== '') {
                return $this->normalizeProductImageUrl((string) $value);
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


    protected function getProductsForView(): array
    {
        $productQuery = $this->buildProductQuery();

        $this->totalProducts = (clone $productQuery)->count();
        $this->productPerPageValue = $this->productPerPageLimit();
        $this->productLastPage = max(1, (int) ceil($this->totalProducts / $this->productPerPageValue));

        if ($this->productPage > $this->productLastPage) {
            $this->productPage = $this->productLastPage;
        }

        if ($this->productPage < 1) {
            $this->productPage = 1;
        }

        $this->productCurrentPage = $this->productPage;

        $productOffset = ($this->productCurrentPage - 1) * $this->productPerPageValue;

        $this->productFirstItem = $this->totalProducts > 0 ? $productOffset + 1 : 0;
        $this->productLastItem = min($this->totalProducts, $productOffset + $this->productPerPageValue);

        return (clone $productQuery)
            ->skip($productOffset)
            ->take($this->productPerPageValue)
            ->get()
            ->map(fn ($product): array => $this->mapProductForView($product))
            ->all();
    }

    public function getSelectedCategoryForView(): array
    {
        if (! $this->selectedCategoryId || ! $this->tableExists('categories')) {
            return [
                'name' => 'Αρχή Καταλόγου',
                'sites' => [
                    ['name' => 'example.com', 'checked' => true],
                    ['name' => 'b2b.example.com', 'checked' => true],
                ],
                'created_at' => '-',
                'updated_at' => '-',
                'image' => null,
                'active_subcategories' => $this->countRootCategories(),
                'active_products' => $this->countProductsForCategory(null),
                'weight' => '0.000',
                'external_feed_fixed' => '0.00€',
                'external_feed_percentage' => '0.00%',
            ];
        }

        $category = Category::query()->find($this->selectedCategoryId);

        if (! $category) {
            $this->selectedCategoryId = null;

            return $this->getSelectedCategoryForView();
        }

        return [
            'name' => $this->getCategoryName($category),
            'sites' => [
                ['name' => 'example.com', 'checked' => true],
                ['name' => 'b2b.example.com', 'checked' => true],
            ],
            'created_at' => $this->formatDateValue($category->created_at ?? null),
            'updated_at' => $this->formatDateValue($category->updated_at ?? null),
            'image' => $this->getCategoryImage($category),
            'active_subcategories' => $this->countSubcategoriesForCategory($category->id),
            'active_products' => $this->countProductsForCategory($category->id),
            'weight' => $this->getCategoryWeight($category),
            'external_feed_fixed' => $this->getCategoryFeedFixed($category),
            'external_feed_percentage' => $this->getCategoryFeedPercentage($category),
        ];
    }

    public function getCurrentPathForView(): string
    {
        if (! $this->selectedCategoryId || ! $this->tableExists('categories')) {
            return 'Αρχή';
        }

        return $this->getCategoryPathById($this->selectedCategoryId) ?: 'Αρχή';
    }

    public function toggleCategoryFlag(int $categoryId, string $field, bool $value): void
    {
        if (! $this->tableExists('categories')) {
            return;
        }

        $allowedFields = [
            'status',
            'xml_feed',
            'xml_only',
            'filter',
            'boxnow',
            'article11',
        ];

        if (! in_array($field, $allowedFields, true)) {
            return;
        }

        $category = Category::query()->find($categoryId);

        if (! $category) {
            return;
        }

        $column = $this->resolveCategoryFlagColumn($field);

        if (! $column) {
            Notification::make()
                ->title('Δεν βρέθηκε αντίστοιχη στήλη στη βάση.')
                ->danger()
                ->send();

            return;
        }

        Category::query()
            ->whereKey($categoryId)
            ->update([
                $column => $this->categoryFlagDatabaseValue($column, $value),
            ]);

        Notification::make()
            ->title($value ? 'Ενεργοποιήθηκε.' : 'Απενεργοποιήθηκε.')
            ->success()
            ->send();
    }

    protected function resolveCategoryFlagColumn(string $field): ?string
    {
        $map = [
            'status' => ['active', 'status'],
            'xml_feed' => ['xml_feed_enabled', 'xml_feed'],
            'xml_only' => ['xml_only_available', 'xml_only'],
            'filter' => ['filters_enabled', 'filter_enabled', 'filter'],
            'boxnow' => ['boxnow_enabled', 'boxnow'],
            'article11' => ['article_11_enabled', 'article11_enabled', 'article11'],
        ];

        foreach ($map[$field] ?? [] as $column) {
            if ($this->columnExists('categories', $column)) {
                return $column;
            }
        }

        return null;
    }

    protected function categoryFlagDatabaseValue(string $column, bool $value)
    {
        if ($column === 'status') {
            return $value ? 'active' : 'inactive';
        }

        return $value;
    }


    public function toggleProductFlag(int $productId, string $field, bool $value): void
    {
        if (! $this->tableExists('products')) {
            return;
        }

        $allowedFields = [
            'active',
            'boxnow',
            'article11',
        ];

        if (! in_array($field, $allowedFields, true)) {
            return;
        }

        $product = Product::query()->find($productId);

        if (! $product) {
            return;
        }

        $column = $this->resolveProductFlagColumn($field);

        if (! $column) {
            Notification::make()
                ->title('Δεν βρέθηκε αντίστοιχη στήλη στη βάση.')
                ->danger()
                ->send();

            return;
        }

        Product::query()
            ->whereKey($productId)
            ->update([
                $column => $this->productFlagDatabaseValue($column, $value),
            ]);

        Notification::make()
            ->title($value ? 'Ενεργοποιήθηκε.' : 'Απενεργοποιήθηκε.')
            ->success()
            ->send();
    }

    protected function resolveProductFlagColumn(string $field): ?string
    {
        $map = [
            'active' => ['active', 'status'],
            'boxnow' => ['boxnow_enabled', 'boxnow'],
            'article11' => ['article_11_enabled', 'article11_enabled', 'article11'],
        ];

        foreach ($map[$field] ?? [] as $column) {
            if ($this->columnExists('products', $column)) {
                return $column;
            }
        }

        return null;
    }

    protected function productFlagDatabaseValue(string $column, bool $value)
    {
        if ($column === 'status') {
            return $value ? 'active' : 'inactive';
        }

        return $value;
    }

    protected function productFlagValue(Product $product, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! $this->columnExists('products', $column)) {
                continue;
            }

            $value = $product->{$column};

            if ($column === 'status') {
                return in_array(mb_strtolower((string) $value, 'UTF-8'), ['active', 'enabled', 'yes', '1', 'true'], true);
            }

            return in_array($value, [true, 1, '1', 'true', 'yes', 'on', 'active', 'enabled'], true);
        }

        return false;
    }


    protected function buildProductQuery(): Builder
    {
        $query = Product::query();
        $productSearch = $this->productSearchTerm();

        if ($this->categorySearchTerm() !== '' && $productSearch === '') {
            return $query->whereRaw('1 = 0');
        }

        if ($this->columnExists('products', 'category_id')) {
            if ($this->selectedCategoryId && $productSearch === '') {
                $query->where('category_id', $this->selectedCategoryId);
            } elseif ($productSearch === '') {
                $query->where(function (Builder $query): void {
                    $query->whereNull('category_id')->orWhere('category_id', 0);
                });
            }
        }

        if ($productSearch !== '') {
            $search = '%' . $productSearch . '%';

            $query->where(function (Builder $query) use ($search): void {
                foreach (['title', 'code', 'mpn', 'ean'] as $column) {
                    if ($this->columnExists('products', $column)) {
                        $query->orWhere($column, 'like', $search);
                    }
                }
            });
        }

        if ($this->columnExists('products', 'updated_at')) {
            $query->orderByDesc('updated_at');
        } elseif ($this->columnExists('products', 'id')) {
            $query->orderByDesc('id');
        }

        return $query;
    }

    protected function catalogSearchTerm(): string
    {
        return $this->productSearchTerm() ?: $this->categorySearchTerm();
    }

    protected function productSearchTerm(): string
    {
        return trim($this->productSearch);
    }

    protected function categorySearchTerm(): string
    {
        return trim($this->categorySearch);
    }

    protected function mapCategoryForView(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $this->getCategoryName($category),
            'path' => $this->getCategoryPathById((int) $category->id),
            'status' => $this->isCategoryFlagEnabled($category, ['active', 'status']),
            'xml_feed' => $this->isCategoryFlagEnabled($category, ['xml_feed_enabled', 'xml_feed']),
            'xml_only' => $this->isCategoryFlagEnabled($category, ['xml_only_available', 'xml_only']),
            'filter' => $this->isCategoryFlagEnabled($category, ['filters_enabled', 'filter_enabled', 'filter']),
            'boxnow' => $this->isCategoryFlagEnabled($category, ['boxnow_enabled', 'boxnow']),
            'article11' => $this->isCategoryFlagEnabled($category, ['article_11_enabled', 'article11_enabled', 'article11']),
            'sort_order' => $this->getModelValue($category, 'sort_order', '-'),
        ];
    }

    protected function mapProductForView(Product $product): array
    {
        return [
            'image' => $this->productImageForView($product),
            
            'active' => $this->productFlagValue($product, ['active', 'status']),
            'boxnow' => $this->productFlagValue($product, ['boxnow_enabled', 'boxnow']),
            'article11' => $this->productFlagValue($product, ['article_11_enabled', 'article11_enabled', 'article11']),'id' => $product->id,
            'title' => $this->getProductTitle($product),
            'status' => $this->isProductActive($product),
            'code' => $this->getModelValue($product, 'code', '-'),
            'mpn' => $this->getModelValue($product, 'mpn', ''),
            'ean' => $this->getModelValue($product, 'ean', ''),
            'price' => $this->getProductPrice($product),
            'qty' => $this->getModelValue($product, 'quantity', $this->getModelValue($product, 'stock_quantity', '-')),
            'manufacturer' => $this->getBrandName($product),
            'supplier' => $this->getSupplierName($product),
            'category' => $this->getProductCategoryName($product),
            'created_at' => $this->formatDateValue($product->created_at ?? null),
            'updated_at' => $this->formatDateValue($product->updated_at ?? null),
        ];
    }

    protected function isCategoryFlagEnabled(Category $category, array $columns): bool
    {
        foreach ($columns as $column) {
            if ($this->columnExists('categories', $column)) {
                return $this->booleanValue($category->{$column});
            }
        }

        return false;
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
        if (! $model) {
            return $default;
        }

        if (! array_key_exists($column, $model->getAttributes())) {
            return $default;
        }

        return $model->{$column} ?? $default;
    }

    protected function getCategoryName(Category $category): string
    {
        return (string) $this->getModelValue($category, 'name', 'Κατηγορία #' . $category->id);
    }

    protected function getProductTitle(Product $product): string
    {
        return (string) $this->getModelValue($product, 'title', 'Προϊόν #' . $product->id);
    }

    protected function getCategoryImage(Category $category): ?string
    {
        foreach (['category_image', 'image', 'image_path'] as $column) {
            if ($this->columnExists('categories', $column) && ! empty($category->{$column})) {
                return (string) $category->{$column};
            }
        }

        return null;
    }

    protected function getCategoryWeight(Category $category): string
    {
        foreach (['product_weight_multiplier', 'weight', 'weight_kg'] as $column) {
            if ($this->columnExists('categories', $column) && $category->{$column} !== null) {
                return number_format((float) $category->{$column}, 3, '.', '');
            }
        }

        return '0.000';
    }

    protected function getCategoryFeedFixed(Category $category): string
    {
        foreach (['feed_fixed_markup', 'external_feed_fixed', 'fixed_markup'] as $column) {
            if ($this->columnExists('categories', $column) && $category->{$column} !== null) {
                return number_format((float) $category->{$column}, 2, '.', '') . '€';
            }
        }

        return '0.00€';
    }

    protected function getCategoryFeedPercentage(Category $category): string
    {
        foreach (['feed_percent_markup', 'external_feed_percentage', 'percentage_markup'] as $column) {
            if ($this->columnExists('categories', $column) && $category->{$column} !== null) {
                return number_format((float) $category->{$column}, 2, '.', '') . '%';
            }
        }

        return '0.00%';
    }

    protected function countRootCategories(): int
    {
        if (! $this->tableExists('categories')) {
            return 0;
        }

        $query = Category::query();

        if ($this->columnExists('categories', 'parent_id')) {
            $query->where(function (Builder $query): void {
                $query->whereNull('parent_id')->orWhere('parent_id', 0);
            });
        }

        return $query->count();
    }

    protected function countSubcategoriesForCategory(int $categoryId): int
    {
        if (! $this->tableExists('categories') || ! $this->columnExists('categories', 'parent_id')) {
            return 0;
        }

        return Category::query()
            ->where('parent_id', $categoryId)
            ->count();
    }

    protected function countProductsForCategory(?int $categoryId): int
    {
        if (! $this->tableExists('products') || ! $this->columnExists('products', 'category_id')) {
            return 0;
        }

        $query = Product::query();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        } else {
            $query->where(function (Builder $query): void {
                $query->whereNull('category_id')->orWhere('category_id', 0);
            });
        }

        return $query->count();
    }

    protected function getBrandName(Product $product): string
    {
        if ($this->columnExists('products', 'brand_name') && ! empty($product->brand_name)) {
            return (string) $product->brand_name;
        }

        if ($this->columnExists('products', 'manufacturer') && ! empty($product->manufacturer)) {
            return (string) $product->manufacturer;
        }

        if ($this->columnExists('products', 'brand_id') && $this->tableExists('brands') && ! empty($product->brand_id)) {
            $name = Brand::query()
                ->whereKey($product->brand_id)
                ->value('name');

            return $name ?: '-';
        }

        return '-';
    }

    protected function getSupplierName(Product $product): string
    {
        if ($this->columnExists('products', 'supplier_name') && ! empty($product->supplier_name)) {
            return (string) $product->supplier_name;
        }

        if ($this->columnExists('products', 'primary_supplier_id') && $this->tableExists('suppliers') && ! empty($product->primary_supplier_id)) {
            $name = Supplier::query()
                ->whereKey($product->primary_supplier_id)
                ->value('name');

            return $name ?: '-';
        }

        if ($this->columnExists('products', 'supplier_id') && $this->tableExists('suppliers') && ! empty($product->supplier_id)) {
            $name = Supplier::query()
                ->whereKey($product->supplier_id)
                ->value('name');

            return $name ?: '-';
        }

        return '-';
    }

    protected function getProductCategoryName(Product $product): string
    {
        if ($this->columnExists('products', 'category_name') && ! empty($product->category_name)) {
            return (string) $product->category_name;
        }

        if ($this->columnExists('products', 'category_id') && $this->tableExists('categories') && ! empty($product->category_id)) {
            $name = Category::query()
                ->whereKey($product->category_id)
                ->value('name');

            return $name ?: '-';
        }

        return '-';
    }

    protected function getProductPrice(Product $product): string
    {
        if ($this->tableExists('product_prices') && $this->columnExists('product_prices', 'product_id')) {
            $priceRow = ProductPrice::query()
                ->where('product_id', $product->id)
                ->first();

            if ($priceRow) {
                foreach (['retail_inc_vat', 'discount_inc_vat', 'skroutz_inc_vat', 'wholesale_inc_vat', 'cost_ex_vat'] as $column) {
                    if ($this->columnExists('product_prices', $column) && $priceRow->{$column} !== null) {
                        return $this->formatMoney($priceRow->{$column});
                    }
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

    protected function formatMoney($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return '€' . number_format((float) $value, 2, ',', '.');
    }

    protected function formatDateValue($value): string
    {
        if (! $value) {
            return '-';
        }

        try {
            return $value->format('d/m/Y H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    protected function getCategoryPathById(?int $categoryId): string
    {
        if (! $categoryId || ! $this->tableExists('categories')) {
            return '';
        }

        $category = Category::query()->find($categoryId);

        if (! $category) {
            return '';
        }

        if ($this->columnExists('categories', 'full_path') && ! empty($category->full_path)) {
            return str_replace('>', ' / ', (string) $category->full_path);
        }

        $names = [];
        $current = $category;
        $guard = 0;

        while ($current && $guard < 30) {
            $names[] = $this->getCategoryName($current);

            if (! $this->columnExists('categories', 'parent_id') || empty($current->parent_id)) {
                break;
            }

            $current = Category::query()->find($current->parent_id);
            $guard++;
        }

        return implode(' / ', array_reverse($names));
    }

    protected function getCategoryLevel(?int $categoryId): int
    {
        if (! $categoryId || ! $this->tableExists('categories')) {
            return 0;
        }

        $category = Category::query()->find($categoryId);

        if (! $category) {
            return 0;
        }

        if ($this->columnExists('categories', 'level') && $category->level !== null) {
            return (int) $category->level;
        }

        if (! $this->columnExists('categories', 'parent_id')) {
            return 0;
        }

        $level = 0;
        $current = $category;
        $guard = 0;

        while ($current && ! empty($current->parent_id) && $guard < 30) {
            $level++;
            $current = Category::query()->find($current->parent_id);
            $guard++;
        }

        return $level;
    }

    protected function nextCategorySortOrder(?int $parentId): int
    {
        if (! $this->tableExists('categories') || ! $this->columnExists('categories', 'sort_order')) {
            return 0;
        }

        $query = Category::query();

        if ($this->columnExists('categories', 'parent_id')) {
            if ($parentId) {
                $query->where('parent_id', $parentId);
            } else {
                $query->where(function (Builder $query): void {
                    $query->whereNull('parent_id')->orWhere('parent_id', 0);
                });
            }
        }

        return ((int) $query->max('sort_order')) + 1;
    }

    protected function uniqueSlug(string $table, string $value): string
    {
        $base = Str::slug($value);

        if ($base === '') {
            $base = $table . '-' . Str::random(6);
        }

        $slug = $base;
        $counter = 2;

        while (Schema::hasTable($table) && Schema::hasColumn($table, 'slug') && \DB::table($table)->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function normalizeDecimal(string $value): ?float
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $value = str_replace(['€', ' '], '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    protected function emptyToNull(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
