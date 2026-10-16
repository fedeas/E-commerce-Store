<?php

namespace App\Filament\Pages\Catalog;

use App\Models\StockStatus;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class StockStatuses extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-archive-box';

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $slug = 'catalog/stock-statuses';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.catalog.stock-statuses';

    public ?int $selectedStockStatusId = null;

    public bool $showEditModal = false;

    public ?int $editingStockStatusId = null;

    public string $editName = '';

    public string $editSkroutzXmlName = '';

    public bool $editIsAvailable = false;

    public bool $editIsPurchasable = true;

    public string $editSortOrder = '0';

    public function mount(): void
    {
        if ($this->selectedStockStatusId === null) {
            $first = StockStatus::query()->orderBy('sort_order')->orderBy('id')->value('id');

            if ($first) {
                $this->selectedStockStatusId = (int) $first;
            }
        }
    }

    public function getViewData(): array
    {
        $rows = StockStatus::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (StockStatus $status): array {
                return [
                    'id' => $status->id,
                    'name' => $status->name,
                    'skroutz_xml_name' => $status->skroutz_xml_name,
                    'is_available' => $status->is_available,
                    'is_purchasable' => $status->is_purchasable,
                    'sort_order' => $status->sort_order,
                    'products_count' => $this->countProductsForStatus($status),
                    'created_at' => optional($status->created_at)->format('d/m/Y H:i'),
                    'updated_at' => optional($status->updated_at)->format('d/m/Y H:i'),
                ];
            })
            ->all();

        $selected = collect($rows)->first(fn (array $row): bool => (int) $row['id'] === (int) $this->selectedStockStatusId);

        return [
            'stockStatuses' => $rows,
            'selectedStockStatus' => $selected,
        ];
    }

    public function selectStockStatus(int $id): void
    {
        if ($id <= 0) {
            return;
        }

        $this->selectedStockStatusId = $id;
    }

    public function toggleStockStatusFlag(int $id, string $field, bool $value): void
    {
        if (! in_array($field, ['is_available', 'is_purchasable'], true)) {
            return;
        }

        StockStatus::query()
            ->whereKey($id)
            ->update([
                $field => $value,
            ]);

        $this->selectedStockStatusId = $id;
    }

    public function openCreateModal(): void
    {
        $this->editingStockStatusId = null;
        $this->editName = '';
        $this->editSkroutzXmlName = '';
        $this->editIsAvailable = false;
        $this->editIsPurchasable = true;
        $this->editSortOrder = (string) ((StockStatus::query()->max('sort_order') ?? 0) + 1);
        $this->showEditModal = true;
    }

    public function openEditModal(?int $id = null): void
    {
        $id = $id ?: $this->selectedStockStatusId;

        if (! $id) {
            return;
        }

        $status = StockStatus::query()->find($id);

        if (! $status) {
            return;
        }

        $this->editingStockStatusId = (int) $status->id;
        $this->editName = (string) $status->name;
        $this->editSkroutzXmlName = (string) ($status->skroutz_xml_name ?? '');
        $this->editIsAvailable = (bool) $status->is_available;
        $this->editIsPurchasable = (bool) $status->is_purchasable;
        $this->editSortOrder = (string) $status->sort_order;
        $this->selectedStockStatusId = (int) $status->id;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function saveStockStatus(): void
    {
        $name = trim($this->editName);

        if ($name === '') {
            return;
        }

        $data = [
            'name' => $name,
            'skroutz_xml_name' => trim($this->editSkroutzXmlName) !== '' ? trim($this->editSkroutzXmlName) : null,
            'is_available' => $this->editIsAvailable,
            'is_purchasable' => $this->editIsPurchasable,
            'sort_order' => (int) $this->editSortOrder,
        ];

        if ($this->editingStockStatusId) {
            StockStatus::query()
                ->whereKey($this->editingStockStatusId)
                ->update($data);

            $this->selectedStockStatusId = $this->editingStockStatusId;
        } else {
            $status = StockStatus::query()->create($data);

            $this->selectedStockStatusId = (int) $status->id;
        }

        $this->showEditModal = false;
    }

    public function deleteSelectedStockStatus(): void
    {
        if (! $this->selectedStockStatusId) {
            return;
        }

        if ($this->countProductsForStatusId($this->selectedStockStatusId) > 0) {
            return;
        }

        StockStatus::query()
            ->whereKey($this->selectedStockStatusId)
            ->delete();

        $this->selectedStockStatusId = StockStatus::query()->orderBy('sort_order')->orderBy('id')->value('id');
    }

    private function countProductsForStatus(StockStatus $status): int
    {
        return $this->countProductsForStatusId((int) $status->id, (string) $status->name);
    }

    private function countProductsForStatusId(int $id, ?string $name = null): int
    {
        if (! Schema::hasTable('products')) {
            return 0;
        }

        if (Schema::hasColumn('products', 'stock_status_id')) {
            return (int) DB::table('products')
                ->where('stock_status_id', $id)
                ->count();
        }

        if ($name !== null && Schema::hasColumn('products', 'stock_text')) {
            return (int) DB::table('products')
                ->where('stock_text', $name)
                ->count();
        }

        return 0;
    }
}
