@props([
    'drawerCategories' => [],
    'categoryUrl',
    'copy' => [],
    'homeUrl' => '/',
])

@include('storefront.partials.site-header-style-tag')

@php($brandLogoUrl = asset('images/brand/logo.svg'))

<div class="catalog-overlay" aria-hidden="true">
    <div class="catalog-shell" role="dialog" aria-modal="true" aria-label="Product catalog">
        <aside class="drawer-side">
            <div class="drawer-top">
                <a href="{{ $homeUrl }}" class="brand">
                    <span class="brand-logo">
                        <img src="{{ $brandLogoUrl }}" alt="" width="190" height="56">
                    </span>
                    <span class="sr-only">{{ $copy['category'] ?? 'Catalog' }}</span>
                </a>
                <button class="drawer-close js-close-catalog" type="button" aria-label="Close">×</button>
            </div>

            <div class="drawer-list" role="tablist" aria-label="Catalog categories">
                @foreach($drawerCategories as $category)
                    <button
                        class="drawer-cat-button {{ $loop->first ? 'is-active' : '' }}"
                        type="button"
                        data-panel="catalog-panel-{{ $loop->index }}"
                        role="tab"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    >
                        <span class="cat-icon">{{ mb_substr($category['name'], 0, 1) }}</span>
                        <span class="cat-name">{{ $category['name'] }}</span>
                        <span class="cat-count">›</span>
                    </button>
                @endforeach
            </div>
        </aside>

        <div class="drawer-main">
            @foreach($drawerCategories as $category)
                <section class="drawer-panel {{ $loop->first ? 'is-active' : '' }}" data-panel="catalog-panel-{{ $loop->index }}" role="tabpanel">
                    <div class="panel-content">
                        <div class="panel-head">
                            <div>
                                <h2>{{ $category['name'] }}</h2>
                                <p>{{ count($category['children'] ?? []) }} {{ $copy['sections_available'] ?? 'sections available' }}</p>
                            </div>
                            <a class="view-all" href="{{ $categoryUrl($category) }}">{{ $copy['view'] ?? 'View' }}</a>
                        </div>

                        <div class="mega-category-grid">
                            @forelse($category['children'] ?? [] as $child)
                                @if(!empty($child['children']))
                                    <div class="mega-group">
                                        <a class="mega-group-head" href="{{ $categoryUrl($child) }}">
                                            <span>{{ $child['name'] }}</span>
                                            <em>›</em>
                                        </a>

                                        <div class="mega-links">
                                            @foreach($child['children'] as $grandchild)
                                                <a href="{{ $categoryUrl($grandchild) }}">
                                                    <span>{{ $grandchild['name'] }}</span>
                                                    @if($grandchild['count'] ?? null)
                                                        <small>{{ number_format($grandchild['count']) }}</small>
                                                    @endif
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <a class="mega-leaf" href="{{ $categoryUrl($child) }}">
                                        <span>{{ $child['name'] }}</span>
                                        <em>›</em>
                                    </a>
                                @endif
                            @empty
                                <a class="mega-leaf" href="{{ $categoryUrl($category) }}">
                                    <span>All {{ $category['name'] }}</span>
                                    <em>›</em>
                                </a>
                            @endforelse
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    </div>
</div>
