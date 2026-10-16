<?php

namespace App\Filament\Pages\Catalog;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class MenuManager extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';

    protected static ?string $navigationLabel = 'Διαχείριση μενού';

    protected static ?string $title = 'Στοιχεία Μενού';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bars-3-bottom-left';

    protected static ?int $navigationSort = 23;

    protected string $view = 'filament.pages.catalog.menu-manager';

    public string $domainFilter = 'all';

    public string $search = '';

    public ?int $selectedMenuId = null;

    protected array $queryString = [
        'domainFilter' => ['except' => 'all', 'as' => 'site'],
        'search' => ['except' => '', 'as' => 'q'],
        'selectedMenuId' => ['except' => null, 'as' => 'mID'],
    ];

    protected function getViewData(): array
    {
        $items = $this->menuItemsForView();

        if ($this->selectedMenuId === null && $items !== []) {
            $this->selectedMenuId = (int) $items[0]['id'];
        }

        return [
            'items' => $items,
            'selectedItem' => collect($items)->first(fn (array $item): bool => (int) $item['id'] === (int) $this->selectedMenuId),
            'domains' => $this->domainOptions(),
            'totalItems' => count($items),
            'tableReady' => Schema::hasTable('site_menu_items'),
        ];
    }

    public function setDomainFilter(string $domain): void
    {
        $this->domainFilter = in_array($domain, ['all', 'example.com', 'b2b.example.com'], true) ? $domain : 'all';
        $this->selectedMenuId = null;
    }

    public function selectMenuItem(int $id): void
    {
        if ($id > 0) {
            $this->selectedMenuId = $id;
        }
    }

    public function toggleStatus(int $id, bool $active): void
    {
        if (! Schema::hasTable('site_menu_items') || $id <= 0) {
            return;
        }

        DB::table('site_menu_items')
            ->where('id', $id)
            ->update([
                'is_active' => $active,
                'updated_at' => now(),
            ]);

        Notification::make()
            ->title($active ? 'Το στοιχείο μενού ενεργοποιήθηκε.' : 'Το στοιχείο μενού απενεργοποιήθηκε.')
            ->success()
            ->send();
    }

    protected function menuItemsForView(): array
    {
        if (! Schema::hasTable('site_menu_items')) {
            return $this->fallbackMenuItems();
        }

        $query = DB::table('site_menu_items');

        if (trim($this->search) !== '') {
            $search = '%' . trim($this->search) . '%';
            $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', $search)
                    ->orWhere('position', 'like', $search)
                    ->orWhere('slug', 'like', $search);
            });
        }

        $items = $query
            ->orderBy('position')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(fn ($item): array => $this->mapMenuItem((array) $item))
            ->all();

        if ($this->domainFilter === 'all') {
            return $items;
        }

        return array_values(array_filter(
            $items,
            fn (array $item): bool => in_array($this->domainFilter, $item['sites'], true),
        ));
    }

    protected function mapMenuItem(array $item): array
    {
        $sites = $item['sites'] ?? [];

        if (is_string($sites)) {
            $decoded = json_decode($sites, true);
            $sites = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', $sites)));
        }

        return [
            'id' => (int) ($item['id'] ?? 0),
            'title' => (string) ($item['title'] ?? '-'),
            'slug' => (string) ($item['slug'] ?? ''),
            'position' => (string) ($item['position'] ?? '-'),
            'sites' => array_values($sites),
            'sort_order' => (int) ($item['sort_order'] ?? 0),
            'is_active' => (bool) ($item['is_active'] ?? false),
            'link_url' => (string) ($item['link_url'] ?? ''),
            'updated_at' => (string) ($item['updated_at'] ?? ''),
        ];
    }

    protected function domainOptions(): array
    {
        return [
            'all' => 'Όλα τα site',
            'example.com' => 'example.com',
            'b2b.example.com' => 'b2b.example.com',
        ];
    }

    protected function fallbackMenuItems(): array
    {
        return [
            ['id' => 149, 'title' => 'HOME', 'slug' => 'home', 'position' => 'Πάνω', 'sites' => ['example.com', 'b2b.example.com'], 'sort_order' => -99, 'is_active' => true, 'link_url' => '/'],
            ['id' => 146, 'title' => 'NEW PRODUCTS', 'slug' => 'new-products', 'position' => 'Πάνω', 'sites' => [], 'sort_order' => 1, 'is_active' => true, 'link_url' => ''],
            ['id' => 100, 'title' => 'OUR COMPANY', 'slug' => 'our-company', 'position' => 'Πάνω', 'sites' => ['example.com', 'b2b.example.com'], 'sort_order' => 1, 'is_active' => true, 'link_url' => ''],
            ['id' => 132, 'title' => 'FLASH SALES', 'slug' => 'flash-sales', 'position' => 'Πάνω', 'sites' => [], 'sort_order' => 20, 'is_active' => true, 'link_url' => ''],
            ['id' => 145, 'title' => 'WHOLESALE', 'slug' => 'wholesale', 'position' => 'Πάνω', 'sites' => ['example.com'], 'sort_order' => 30, 'is_active' => false, 'link_url' => ''],
            ['id' => 81, 'title' => 'Shipping', 'slug' => 'shipping', 'position' => 'Κάτω Παραγγελία', 'sites' => ['example.com', 'b2b.example.com'], 'sort_order' => 0, 'is_active' => true, 'link_url' => '/menu.php?menu_id=81'],
        ];
    }
}
