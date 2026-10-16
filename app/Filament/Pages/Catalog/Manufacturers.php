<?php

namespace App\Filament\Pages\Catalog;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use UnitEnum;

class Manufacturers extends Page
{
    use WithPagination;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $slug = 'catalog/manufacturers';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.catalog.manufacturers';

    public string $search = '';

    public string $perPage = '50';

    public ?int $selectedManufacturerId = null;

    public bool $showEditModal = false;

    public ?int $editingManufacturerId = null;

    public string $editName = '';

    public string $editSlug = '';

    public string $editWebsiteUrl = '';

    public string $editImagePath = '';

    public bool $editActive = true;

    public string $editSortOrder = '0';

    public function mount(): void
    {
        if ($this->selectedManufacturerId === null && Schema::hasTable('brands')) {
            $query = DB::table('brands');

            if (Schema::hasColumn('brands', 'sort_order')) {
                $query->orderBy('sort_order');
            }

            $firstId = $query->orderBy('name')->value('id');

            if ($firstId) {
                $this->selectedManufacturerId = (int) $firstId;
            }
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (! in_array($this->perPage, ['25', '50', '100', '300'], true)) {
            $this->perPage = '50';
        }

        $this->resetPage();
    }

    public function getViewData(): array
    {
        if (! Schema::hasTable('brands')) {
            return [
                'manufacturers' => [],
                'selectedManufacturer' => null,
                'totalManufacturers' => 0,
            ];
        }

        $query = DB::table('brands');

        if (trim($this->search) !== '') {
            $search = '%' . trim($this->search) . '%';

            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', $search);

                if (Schema::hasColumn('brands', 'slug')) {
                    $query->orWhere('slug', 'like', $search);
                }

                if (Schema::hasColumn('brands', 'website_url')) {
                    $query->orWhere('website_url', 'like', $search);
                }
            });
        }

        if (Schema::hasColumn('brands', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        $query->orderBy('name');

        $manufacturers = $query->paginate((int) $this->perPage);

        $manufacturers->setCollection(
            $manufacturers->getCollection()->map(function ($brand): array {
                return $this->mapBrandForView($brand);
            })
        );

        $selected = $manufacturers
            ->getCollection()
            ->first(fn (array $row): bool => (int) $row['id'] === (int) $this->selectedManufacturerId);

        if (! $selected && $this->selectedManufacturerId) {
            $brand = DB::table('brands')
                ->where('id', $this->selectedManufacturerId)
                ->first();

            if ($brand) {
                $selected = $this->mapBrandForView($brand);
            }
        }

        return [
            'manufacturers' => $manufacturers,
            'selectedManufacturer' => $selected,
            'totalManufacturers' => $manufacturers->total(),
        ];
    }

    public function selectManufacturer(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        $this->selectedManufacturerId = $id;
    }

    public function toggleManufacturerStatus(int $id, bool $value): void
    {
        if ($id <= 0 || ! Schema::hasTable('brands')) {
            return;
        }

        $updates = [];

        if (Schema::hasColumn('brands', 'active')) {
            $updates['active'] = $value ? 1 : 0;
        }

        if (Schema::hasColumn('brands', 'status')) {
            $updates['status'] = $value ? 'active' : 'inactive';
        }

        if ($updates === []) {
            return;
        }

        DB::table('brands')
            ->where('id', $id)
            ->update($updates);

        $this->selectedManufacturerId = $id;
    }

    public function openCreateModal(): void
    {
        $this->editingManufacturerId = null;
        $this->editName = '';
        $this->editSlug = '';
        $this->editWebsiteUrl = '';
        $this->editImagePath = '';
        $this->editActive = true;
        $this->editSortOrder = Schema::hasTable('brands') && Schema::hasColumn('brands', 'sort_order')
            ? (string) (((int) DB::table('brands')->max('sort_order')) + 1)
            : '0';
        $this->showEditModal = true;
    }

    public function openEditModal(?int $id = null): void
    {
        $id = $id ?: $this->selectedManufacturerId;

        if (! $id || ! Schema::hasTable('brands')) {
            return;
        }

        $brand = DB::table('brands')->where('id', $id)->first();

        if (! $brand) {
            return;
        }

        $this->editingManufacturerId = (int) $brand->id;
        $this->editName = (string) ($brand->name ?? '');
        $this->editSlug = property_exists($brand, 'slug') ? (string) ($brand->slug ?? '') : '';
        $this->editWebsiteUrl = property_exists($brand, 'website_url') ? (string) ($brand->website_url ?? '') : '';
        $this->editImagePath = property_exists($brand, 'image_path') ? (string) ($brand->image_path ?? '') : '';
        $this->editActive = $this->brandActiveValue($brand);
        $this->editSortOrder = property_exists($brand, 'sort_order') ? (string) ($brand->sort_order ?? 0) : '0';
        $this->selectedManufacturerId = (int) $brand->id;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function saveManufacturer(): void
    {
        if (! Schema::hasTable('brands')) {
            return;
        }

        $name = trim($this->editName);

        if ($name === '') {
            return;
        }

        $data = [
            'name' => $name,
        ];

        if (Schema::hasColumn('brands', 'slug')) {
            $data['slug'] = trim($this->editSlug) !== '' ? Str::slug(trim($this->editSlug)) : Str::slug($name);
        }

        if (Schema::hasColumn('brands', 'active')) {
            $data['active'] = $this->editActive ? 1 : 0;
        }

        if (Schema::hasColumn('brands', 'website_url')) {
            $data['website_url'] = trim($this->editWebsiteUrl) !== '' ? trim($this->editWebsiteUrl) : null;
        }

        if (Schema::hasColumn('brands', 'image_path')) {
            $data['image_path'] = trim($this->editImagePath) !== '' ? trim($this->editImagePath) : null;
        }

        if (Schema::hasColumn('brands', 'sort_order')) {
            $data['sort_order'] = (int) $this->editSortOrder;
        }

        if (Schema::hasColumn('brands', 'updated_at')) {
            $data['updated_at'] = now();
        }

        if ($this->editingManufacturerId) {
            DB::table('brands')
                ->where('id', $this->editingManufacturerId)
                ->update($data);

            $this->selectedManufacturerId = $this->editingManufacturerId;
        } else {
            if (Schema::hasColumn('brands', 'created_at')) {
                $data['created_at'] = now();
            }

            $id = DB::table('brands')->insertGetId($data);

            $this->selectedManufacturerId = (int) $id;
        }

        $this->showEditModal = false;
    }

    public function deleteSelectedManufacturer(): void
    {
        if (! $this->selectedManufacturerId || ! Schema::hasTable('brands')) {
            return;
        }

        $brand = DB::table('brands')->where('id', $this->selectedManufacturerId)->first();

        if (! $brand) {
            return;
        }

        if ($this->countProductsForBrand((int) $brand->id, (string) $brand->name) > 0) {
            return;
        }

        DB::table('brands')
            ->where('id', $this->selectedManufacturerId)
            ->delete();

        $query = DB::table('brands');

        if (Schema::hasColumn('brands', 'sort_order')) {
            $query->orderBy('sort_order');
        }

        $this->selectedManufacturerId = $query->orderBy('name')->value('id');
    }

    private function mapBrandForView(object $brand): array
    {
        $id = (int) $brand->id;

        return [
            'id' => $id,
            'name' => (string) ($brand->name ?? ''),
            'slug' => property_exists($brand, 'slug') ? (string) ($brand->slug ?? '') : '',
            'active' => $this->brandActiveValue($brand),
            'website_url' => property_exists($brand, 'website_url') ? (string) ($brand->website_url ?? '') : '',
            'image_path' => property_exists($brand, 'image_path') ? (string) ($brand->image_path ?? '') : '',
            'sort_order' => property_exists($brand, 'sort_order') ? (int) ($brand->sort_order ?? 0) : 0,
            'products_count' => $this->countProductsForBrand($id, (string) ($brand->name ?? '')),
            'created_at' => property_exists($brand, 'created_at') && $brand->created_at ? date('d/m/Y H:i', strtotime((string) $brand->created_at)) : '-',
            'updated_at' => property_exists($brand, 'updated_at') && $brand->updated_at ? date('d/m/Y H:i', strtotime((string) $brand->updated_at)) : '-',
        ];
    }

    private function countProductsForBrand(int $brandId, string $brandName): int
    {
        if (! Schema::hasTable('products')) {
            return 0;
        }

        if (Schema::hasColumn('products', 'brand_id')) {
            return (int) DB::table('products')
                ->where('brand_id', $brandId)
                ->count();
        }

        if (Schema::hasColumn('products', 'manufacturer')) {
            return (int) DB::table('products')
                ->where('manufacturer', $brandName)
                ->count();
        }

        if (Schema::hasColumn('products', 'brand')) {
            return (int) DB::table('products')
                ->where('brand', $brandName)
                ->count();
        }

        return 0;
    }

    private function brandActiveValue(object $brand): bool
    {
        if (property_exists($brand, 'active')) {
            return in_array($brand->active, [true, 1, '1', 'true', 'yes', 'on', 'active', 'enabled'], true);
        }

        if (property_exists($brand, 'status')) {
            return in_array($brand->status, [true, 1, '1', 'true', 'yes', 'on', 'active', 'enabled'], true);
        }

        return true;
    }
}
