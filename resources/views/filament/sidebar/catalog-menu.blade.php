@php
    $items = [
        ['label' => 'Κατηγορίες / Προϊόντα', 'url' => \App\Filament\Pages\Catalog\CategoriesProducts::getUrl()],
        ['label' => 'Λεκτικά stock', 'url' => \App\Filament\Pages\Catalog\StockStatuses::getUrl()],
        ['label' => 'Κατασκευαστές', 'url' => \App\Filament\Pages\Catalog\Manufacturers::getUrl()],
        ['label' => 'Γρήγορη ανανέωση', 'url' => \App\Filament\Pages\Catalog\QuickUpdate::getUrl()],
        ['label' => 'Ενημέρωση ειδών από excel', 'url' => \App\Filament\Pages\Catalog\ExcelProductUpdate::getUrl()],
        ['label' => 'Μαζική μετακίνηση ή διαγραφή', 'url' => \App\Filament\Pages\Catalog\BulkMoveDelete::getUrl()],
        ['label' => 'Διαχείριση μενού', 'url' => \App\Filament\Pages\Catalog\MenuManager::getUrl()],
        ['label' => 'Κριτικές', 'url' => \App\Filament\Pages\Catalog\Reviews::getUrl()],
        ['label' => 'Έξτρα πεδία', 'url' => \App\Filament\Pages\Catalog\ExtraFields::getUrl()],
        ['label' => 'Εικόνες αρχικής / banners', 'url' => \App\Filament\Pages\Catalog\HomeImagesBanners::getUrl()],
        ['label' => 'Προσφορές', 'url' => \App\Filament\Pages\Catalog\Offers::getUrl()],
        ['label' => 'Προτεινόμενα', 'url' => \App\Filament\Pages\Catalog\RecommendedProducts::getUrl()],
    ];

    $customerItems = [
        ['label' => 'Πελάτες', 'url' => \App\Filament\Pages\Customers::getUrl()],
    ];

    $supplierItems = [
        ['label' => 'XML Προμηθευτών', 'url' => \App\Filament\Pages\SupplierBridges::getUrl()],
    ];

    $currentUrl = url()->current();
    $isOpenByDefault = collect($items)->contains(fn ($item) => $item['url'] === $currentUrl);
    $isCustomersOpenByDefault = collect($customerItems)->contains(fn ($item) => $item['url'] === $currentUrl);
    $isSuppliersOpenByDefault = collect($supplierItems)->contains(fn ($item) => $item['url'] === $currentUrl);
@endphp

<div x-data="{ open: {{ $isOpenByDefault ? 'true' : 'false' }}, customersOpen: {{ $isCustomersOpenByDefault ? 'true' : 'false' }}, suppliersOpen: {{ $isSuppliersOpenByDefault ? 'true' : 'false' }} }" class="cl-nav-root" x-cloak>
    <button
        type="button"
        class="cl-sidebar-collapse-btn"
        title="Κλείσιμο / άνοιγμα sidebar"
        @click.stop="
            if (typeof $store.sidebar.toggle === 'function') {
                $store.sidebar.toggle()
            } else if ($store.sidebar.isOpen) {
                if (typeof $store.sidebar.close === 'function') {
                    $store.sidebar.close()
                } else {
                    $store.sidebar.isOpen = false
                }
            } else {
                if (typeof $store.sidebar.open === 'function') {
                    $store.sidebar.open()
                } else {
                    $store.sidebar.isOpen = true
                }
            }
        "
    >
        <span x-show="$store.sidebar.isOpen" x-transition>← Κλείσιμο sidebar</span>
        <span x-show="! $store.sidebar.isOpen" x-transition>☰</span>
    </button>

    <div class="cl-nav-section-title" x-show="$store.sidebar.isOpen" x-transition>
        Κεντρικό Μενού
    </div>

    <div class="cl-nav-card">
        <button type="button" @click="open = ! open" class="cl-nav-trigger">
            <span class="cl-nav-trigger-left">
                <span class="cl-nav-folder">📁</span>
                <span x-show="$store.sidebar.isOpen" x-transition class="cl-nav-trigger-label">Κατάλογος</span>
            </span>

            <svg
                x-show="$store.sidebar.isOpen"
                class="cl-nav-chevron"
                :class="{ 'rotate-180': open }"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.126l3.71-3.895a.75.75 0 1 1 1.08 1.04l-4.25 4.463a.75.75 0 0 1-1.08 0L5.21 8.271a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="open && $store.sidebar.isOpen" x-transition class="cl-nav-list">
            @foreach ($items as $item)
                <a href="{{ $item['url'] }}" class="cl-nav-item {{ $currentUrl === $item['url'] ? 'is-active' : '' }}">
                    <span class="cl-nav-dot"></span>
                    <span class="cl-nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div
        x-show="open && ! $store.sidebar.isOpen"
        x-transition
        @click.away="open = false"
        class="cl-nav-flyout"
    >
        <div class="cl-nav-flyout-head">
            <div class="cl-nav-flyout-title">Κατάλογος</div>

            <button type="button" class="cl-nav-flyout-close" @click="open = false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 8.94 5.53 4.47a.75.75 0 0 0-1.06 1.06L8.94 10l-4.47 4.47a.75.75 0 1 0 1.06 1.06L10 11.06l4.47 4.47a.75.75 0 0 0 1.06-1.06L11.06 10l4.47-4.47a.75.75 0 0 0-1.06-1.06L10 8.94Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div class="cl-nav-flyout-body">
            <div class="cl-nav-flyout-grid">
                @foreach ($items as $item)
                    <a href="{{ $item['url'] }}" class="cl-nav-flyout-item {{ $currentUrl === $item['url'] ? 'is-active' : '' }}">
                        <span class="cl-nav-dot"></span>
                        <span class="cl-nav-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="cl-nav-card cl-nav-card-spaced">
        <button type="button" @click="customersOpen = ! customersOpen" class="cl-nav-trigger">
            <span class="cl-nav-trigger-left">
                <span class="cl-nav-folder">👥</span>
                <span x-show="$store.sidebar.isOpen" x-transition class="cl-nav-trigger-label">Πελάτες</span>
            </span>

            <svg
                x-show="$store.sidebar.isOpen"
                class="cl-nav-chevron"
                :class="{ 'rotate-180': customersOpen }"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.126l3.71-3.895a.75.75 0 1 1 1.08 1.04l-4.25 4.463a.75.75 0 0 1-1.08 0L5.21 8.271a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="customersOpen && $store.sidebar.isOpen" x-transition class="cl-nav-list">
            @foreach ($customerItems as $item)
                <a href="{{ $item['url'] }}" class="cl-nav-item {{ $currentUrl === $item['url'] ? 'is-active' : '' }}">
                    <span class="cl-nav-dot"></span>
                    <span class="cl-nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div
        x-show="customersOpen && ! $store.sidebar.isOpen"
        x-transition
        @click.away="customersOpen = false"
        class="cl-nav-flyout cl-nav-flyout-compact"
    >
        <div class="cl-nav-flyout-head">
            <div class="cl-nav-flyout-title">Πελάτες</div>

            <button type="button" class="cl-nav-flyout-close" @click="customersOpen = false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 8.94 5.53 4.47a.75.75 0 0 0-1.06 1.06L8.94 10l-4.47 4.47a.75.75 0 1 0 1.06 1.06L10 11.06l4.47 4.47a.75.75 0 0 0 1.06-1.06L11.06 10l4.47-4.47a.75.75 0 0 0-1.06-1.06L10 8.94Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div class="cl-nav-flyout-body">
            <div class="cl-nav-flyout-grid">
                @foreach ($customerItems as $item)
                    <a href="{{ $item['url'] }}" class="cl-nav-flyout-item {{ $currentUrl === $item['url'] ? 'is-active' : '' }}">
                        <span class="cl-nav-dot"></span>
                        <span class="cl-nav-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="cl-nav-card cl-nav-card-spaced">
        <button type="button" @click="suppliersOpen = ! suppliersOpen" class="cl-nav-trigger">
            <span class="cl-nav-trigger-left">
                <span class="cl-nav-folder">🚚</span>
                <span x-show="$store.sidebar.isOpen" x-transition class="cl-nav-trigger-label">Προμηθευτές</span>
            </span>

            <svg
                x-show="$store.sidebar.isOpen"
                class="cl-nav-chevron"
                :class="{ 'rotate-180': suppliersOpen }"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.126l3.71-3.895a.75.75 0 1 1 1.08 1.04l-4.25 4.463a.75.75 0 0 1-1.08 0L5.21 8.271a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
            </svg>
        </button>

        <div x-show="suppliersOpen && $store.sidebar.isOpen" x-transition class="cl-nav-list">
            @foreach ($supplierItems as $item)
                <a href="{{ $item['url'] }}" class="cl-nav-item {{ $currentUrl === $item['url'] ? 'is-active' : '' }}">
                    <span class="cl-nav-dot"></span>
                    <span class="cl-nav-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div
        x-show="suppliersOpen && ! $store.sidebar.isOpen"
        x-transition
        @click.away="suppliersOpen = false"
        class="cl-nav-flyout cl-nav-flyout-compact"
    >
        <div class="cl-nav-flyout-head">
            <div class="cl-nav-flyout-title">Προμηθευτές</div>

            <button type="button" class="cl-nav-flyout-close" @click="suppliersOpen = false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 8.94 5.53 4.47a.75.75 0 0 0-1.06 1.06L8.94 10l-4.47 4.47a.75.75 0 1 0 1.06 1.06L10 11.06l4.47 4.47a.75.75 0 0 0 1.06-1.06L11.06 10l4.47-4.47a.75.75 0 0 0-1.06-1.06L10 8.94Z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <div class="cl-nav-flyout-body">
            <div class="cl-nav-flyout-grid">
                @foreach ($supplierItems as $item)
                    <a href="{{ $item['url'] }}" class="cl-nav-flyout-item {{ $currentUrl === $item['url'] ? 'is-active' : '' }}">
                        <span class="cl-nav-dot"></span>
                        <span class="cl-nav-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
