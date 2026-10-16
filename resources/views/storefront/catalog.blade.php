<!DOCTYPE html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} | {{ config('app.name') }}</title>
    @include('storefront.partials.site-fonts')
    <style>
        :root { --ink:#20242c; --muted:#667085; --line:#d9e0e8; --soft:#f3f6f8; --dark:#18212c; --green:#00a6a6; --green-2:#0d6f7f; --blue:#2f6fed; --red:#c2413a; --radius:8px; }
        @include('storefront.partials.site-font-styles')
        * { box-sizing:border-box; }
        body { margin:0; font-family:var(--font-sans); background:#eef2f5; color:var(--ink); letter-spacing:0; }
        a { color:inherit; text-decoration:none; }
        img { display:block; max-width:100%; }
        .wrap { width:min(1250px, calc(100% - 32px)); margin:0 auto; }
        .top { background:#18212c; color:#fff; border-bottom:1px solid rgba(0,166,166,.55); }
        .top .wrap { min-height:31px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
        .header { background:#fff; border-bottom:1px solid var(--line); }
        .header .wrap { min-height:70px; display:grid; grid-template-columns:230px minmax(260px, 1fr) auto; align-items:center; gap:16px; }
        .brand { display:flex; align-items:center; gap:10px; min-width:0; }
        .brand strong { display:block; font-size:20px; line-height:1; font-weight:900; }
        .brand span span { display:block; margin-top:4px; color:var(--muted); font-size:11px; font-weight:900; text-transform:uppercase; }
        .search { height:44px; display:grid; grid-template-columns:minmax(0, 1fr) 48px; border:2px solid #d5dce3; border-radius:var(--radius); overflow:hidden; background:#fff; }
        .search input, .search button { min-width:0; border:0; outline:0; background:#fff; font:inherit; }
        .search input { padding:0 15px; font-size:13px; font-weight:700; }
        .search button { background:#00a6a6; color:#fff; font-weight:900; }
        .header-actions { display:flex; gap:8px; justify-content:flex-end; }
        .header-actions a { min-height:42px; display:inline-flex; align-items:center; padding:0 12px; border:1px solid var(--line); border-radius:var(--radius); font-size:12px; font-weight:900; }
        .cart { background:var(--dark); color:#fff; border-color:var(--dark) !important; }
        main { padding:22px 0 50px; }
        .hero { padding:26px; border-radius:var(--radius); background:linear-gradient(135deg,#18212c,#223242 55%,#102532); color:#fff; border:1px solid rgba(255,255,255,.08); }
        .hero small { color:#7dd3fc; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:1.6px; }
        .hero h1 { margin:8px 0 8px; font-size:34px; line-height:1.08; }
        .hero p { margin:0; color:#c4cbd3; font-size:14px; font-weight:700; }
        .catalog-tools { margin-top:18px; display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; }
        .tool-card { min-height:86px; padding:14px; border:1px solid var(--line); border-radius:var(--radius); background:#fff; }
        .tool-card small { color:#6c7783; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:1px; }
        .tool-card strong { display:block; margin-top:8px; font-size:18px; line-height:1.1; }
        .tool-card span { display:block; margin-top:6px; color:#667085; font-size:12px; font-weight:750; }
        .layout { margin-top:18px; display:grid; grid-template-columns:248px minmax(0,1fr); gap:16px; align-items:start; }
        .sidebar, .panel, .product-card { border:1px solid var(--line); border-radius:var(--radius); background:#fff; box-shadow:none; }
        .sidebar { padding:12px; position:sticky; top:88px; max-height:calc(100dvh - 106px); overflow-y:auto; overscroll-behavior:contain; scrollbar-width:thin; }
        .sidebar::-webkit-scrollbar { width:8px; }
        .sidebar::-webkit-scrollbar-track { background:#eef4f8; border-radius:20px; }
        .sidebar::-webkit-scrollbar-thumb { background:#c6d4df; border-radius:20px; }
        .side-title { margin:0 0 10px; font-size:12px; font-weight:900; text-transform:uppercase; color:#56616d; }
        .side-link { min-height:39px; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:0 10px; border-radius:var(--radius); color:#2b3640; font-size:12px; font-weight:900; }
        .side-link:hover, .side-link.is-active { background:#eaf1ff; color:#2f6fed; }
        .side-help { margin-top:14px; padding:14px; border-radius:var(--radius); background:#18212c; color:#fff; }
        .side-help small { color:#7dd3fc; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:1px; }
        .side-help strong { display:block; margin-top:8px; font-size:15px; line-height:1.25; }
        .side-help p { margin:8px 0 0; color:#c4cbd3; font-size:12px; line-height:1.5; font-weight:700; }
        .panel { padding:14px; }
        .panel-head { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:14px; }
        .panel-head strong { font-size:14px; text-transform:uppercase; }
        .panel-head span { color:var(--muted); font-size:12px; font-weight:800; }
        .pager { margin-top:16px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .panel-head + .filter-row + .pager,
        .subcats + .pager { margin-top:0; margin-bottom:14px; padding:10px 12px; border:1px solid #e1e8ef; border-radius:var(--radius); background:#f7fafc; }
        .pager span { color:var(--muted); font-size:12px; font-weight:850; }
        .pager-actions {
            display:flex;
            align-items:center;
            gap:8px;
        }
        .pager-actions .gallery-nav,
        .pager-actions .pager-nav-placeholder {
            width:38px;
            height:38px;
            display:grid;
            place-items:center;
            border:1px solid #cbd6e2;
            border-radius:var(--radius);
            background:#fff;
            color:#18212c;
            font-size:18px;
            font-weight:900;
            box-shadow:0 6px 16px rgba(24,33,44,.08);
            text-decoration:none;
            cursor:pointer;
            transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }
        .pager-actions .gallery-nav:hover {
            border-color:#18212c;
            box-shadow:0 10px 22px rgba(24,33,44,.12);
            transform:translateY(-1px);
        }
        .pager-actions .pager-nav-placeholder {
            color:#9aa8b6;
            cursor:not-allowed;
            opacity:.58;
            box-shadow:none;
        }
        .pager-actions .pager-number {
            min-width:58px;
            height:18px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border:0;
            border-radius:999px;
            background:#00a6a6;
            color:#fff;
            box-shadow:none;
            font-size:11px;
            font-weight:950;
            line-height:1;
        }
        .pager-actions .pager-nav-placeholder {
            opacity:.35;
            pointer-events:none;
        }
        .filter-row { margin-bottom:14px; display:flex; flex-wrap:wrap; gap:8px; }
        .filter-chip { min-height:34px; display:inline-flex; align-items:center; padding:0 11px; border:1px solid #dce5ef; border-radius:999px; color:#344253; background:#fff; font-size:12px; font-weight:900; }
        .filter-chip.is-active { background:#18212c; color:#fff; border-color:#18212c; }
        .sidebar .dynamic-filters { margin:0; display:grid; gap:9px; }
        .filter-group { border:1px solid #e1e8ef; border-radius:var(--radius); background:#f8fbfd; overflow:hidden; }
        .filter-group summary { min-height:42px; display:grid; grid-template-columns:minmax(0, 1fr) 24px; align-items:center; gap:10px; padding:0 9px 0 11px; cursor:pointer; list-style:none; }
        .filter-group summary::-webkit-details-marker { display:none; }
        .filter-group summary:after { content:"+"; width:24px; height:24px; display:flex; align-items:center; justify-content:center; border-radius:50%; background:#fff; color:#18212c; font-size:15px; line-height:1; font-weight:950; border:1px solid #d7e2eb; transform:translateY(0); }
        .filter-group[open] summary:after { content:"−"; background:#18212c; color:#fff; border-color:#18212c; }
        .filter-group-head { display:flex; min-width:0; flex-direction:column; gap:2px; }
        .filter-group strong { font-size:12px; font-weight:950; color:#18212c; }
        .filter-group small { color:#788695; font-size:10px; font-weight:900; text-transform:uppercase; }
        .filter-options { display:grid; gap:7px; padding:0 11px 11px; }
        .filter-option { min-height:32px; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:0 10px; border:1px solid #d7e2eb; border-radius:var(--radius); background:#fff; color:#334155; font-size:11px; font-weight:900; }
        .filter-option em { color:#7b8794; font-size:10px; font-style:normal; }
        .filter-option.is-active { background:#18212c; border-color:#18212c; color:#fff; }
        .filter-option.is-active em { color:#c6d3df; }
        .filter-clear { min-height:34px; display:flex; align-items:center; justify-content:center; margin-bottom:10px; border:1px solid #cbd6e2; border-radius:var(--radius); background:#fff; color:#2f6fed; font-size:11px; font-weight:900; }
        .section-links { display:grid; gap:8px; }
        .section-link { min-height:38px; display:flex; align-items:center; justify-content:space-between; gap:9px; padding:0 10px; border:1px solid #d7e2eb; border-radius:var(--radius); background:#fff; color:#253344; font-size:11px; font-weight:950; }
        .section-link em { width:22px; height:22px; display:grid; place-items:center; border-radius:50%; background:#eaf1ff; color:#2f6fed; font-style:normal; font-size:13px; }
        .section-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:14px; }
        .section-tile {
            min-height:158px;
            padding:16px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:
                linear-gradient(135deg, rgba(255,255,255,.96), rgba(247,251,252,.94)),
                radial-gradient(circle at 82% 18%, rgba(0,166,166,.12), transparent 34%);
            color:#18212c;
            overflow:hidden;
            transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }
        .section-tile:hover { transform:translateY(-2px); border-color:#aebfd2; box-shadow:0 14px 28px rgba(24,33,44,.10); }
        .section-icon {
            width:54px;
            height:54px;
            display:grid;
            place-items:center;
            border-radius:15px;
            background:#eaf1ff;
            color:#2f6fed;
            font-size:18px;
            font-weight:950;
        }
        .section-tile strong { margin-top:18px; display:block; font-size:16px; line-height:1.2; overflow-wrap:anywhere; }
        .section-tile span { margin-top:8px; color:var(--muted); font-size:12px; font-weight:850; }
        .section-arrow { align-self:flex-end; width:34px; height:34px; display:grid; place-items:center; border-radius:var(--radius); background:#18212c; color:#fff; font-size:18px; font-weight:900; }
        .grid { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:14px; align-items:stretch; }
        .empty { padding:38px 18px; text-align:center; color:var(--muted); font-weight:800; }
        .product-card { display:flex; flex-direction:column; min-width:0; height:100%; overflow:hidden; transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
        .product-card:hover { transform:translateY(-2px); border-color:#aebfd2; box-shadow:0 14px 28px rgba(24,33,44,.10); }
        .product-media { position:relative; aspect-ratio:4/3; padding:18px; display:grid; place-items:center; background:linear-gradient(135deg,#fff 0%,#f3f6f8 55%,#e9eef5 100%); border-bottom:1px solid var(--line); }
        .product-media a { width:100%; height:100%; display:grid; place-items:center; }
        .product-media img { width:100%; height:100%; object-fit:contain; }
        .product-label, .product-save { position:absolute; top:10px; min-height:24px; display:inline-flex; align-items:center; padding:0 8px; border-radius:20px; font-size:10px; font-weight:900; }
        .product-label { left:10px; background:#eaf1ff; color:#2f6fed; }
        .product-save { right:10px; background:#ffe6e6; color:var(--red); }
        .product-placeholder { width:100%; height:100%; min-height:132px; display:grid; place-items:center; align-content:center; gap:7px; border:1px dashed #cfd9e2; border-radius:var(--radius); background:#fff; color:#6c7783; text-align:center; }
        .product-placeholder strong { width:58px; height:58px; display:grid; place-items:center; border-radius:15px; background:#eaf1ff; color:#2f6fed; font-size:19px; font-weight:900; }
        .product-placeholder span { font-size:10px; font-weight:900; text-transform:uppercase; }
        .product-body { padding:14px; display:flex; flex:1; flex-direction:column; gap:10px; }
        .sku-row { display:flex; align-items:center; justify-content:space-between; gap:8px; color:#7a8996; font-size:10px; font-weight:900; text-transform:uppercase; }
        .sku { min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .stock-dot { color:#0d6f7f; white-space:nowrap; }
        .product-title { margin:0; display:block; overflow:visible; font-size:13px; line-height:1.45; overflow-wrap:anywhere; }
        .product-meta { min-height:26px; display:flex; flex-wrap:wrap; gap:5px; }
        .product-meta span { max-width:100%; min-height:22px; display:inline-flex; align-items:center; padding:0 7px; border-radius:999px; background:#f0f4f7; color:#637180; font-size:10px; font-weight:850; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .price-row { margin-top:auto; display:flex; align-items:end; justify-content:space-between; gap:8px; }
        .price strong { display:block; color:var(--red); font-size:18px; line-height:1.1; white-space:nowrap; }
        .price span { display:block; min-height:16px; margin-top:3px; color:#8b98a3; font-size:12px; font-weight:800; text-decoration:line-through; }
        .price .price-note { display:block; max-width:130px; margin-top:4px; color:#7a8996; font-size:10px; line-height:1.25; font-weight:850; }
        .add-btn { min-width:62px; height:34px; display:grid; place-items:center; padding:0 10px; border:0; border-radius:var(--radius); background:#00a6a6; color:#fff; font:inherit; font-size:11px; font-weight:900; white-space:nowrap; cursor:pointer; }
        @media (max-width:1050px) { .header .wrap { grid-template-columns:1fr; padding:12px 0; } .header-actions { justify-content:flex-start; } .catalog-tools { grid-template-columns:repeat(2,minmax(0,1fr)); } .layout { grid-template-columns:1fr; } .sidebar { position:static; } .grid, .section-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
        @media (max-width:820px) { .grid, .section-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:720px) { .wrap { width:min(100% - 20px, 1250px); } .hero h1 { font-size:28px; } .catalog-tools { grid-template-columns:1fr; } .grid, .section-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .header-actions a:not(.cart) { display:none; } }
        @media (max-width:460px) { .grid, .section-grid { grid-template-columns:1fr; } .panel-head { display:block; } .panel-head span { display:block; margin-top:6px; } }
    </style>
    @include('storefront.partials.site-header-style-tag')
</head>
<body>
@php
    $copy = $copy ?? [];
    $locale = $locale ?? 'en';
    $localePrefix = '/' . $locale;
    $homeUrl = $localePrefix;
    $searchUrl = $localePrefix . '/search';
    $categoryUrl = fn (array $category): string => $localePrefix . '/category/' . ($category['url_path'] ?? $category['slug'] ?? '');
    $pagination = $pagination ?? ['page' => 1, 'perPage' => 30, 'total' => count($products), 'lastPage' => 1, 'from' => count($products) ? 1 : 0, 'to' => count($products), 'hasPrevious' => false, 'hasNext' => false];
    $productTotal = $productTotal ?? count($products);
    $extraFilters = $extraFilters ?? [];
    $selectedFilters = $selectedFilters ?? [];
    $subcategoryTiles = $mode === 'category' ? array_values($currentCategory['children'] ?? []) : [];
    $showSubcategoryTiles = false;
    $baseParams = [];

    if ($mode === 'search' && $query !== '') {
        $baseParams['q'] = $query;
    }

    if (($pagination['perPage'] ?? 30) !== 30) {
        $baseParams['per_page'] = $pagination['perPage'];
    }

    foreach ($selectedFilters as $fieldId => $values) {
        foreach ((array) $values as $value) {
            $baseParams['f'][$fieldId][] = $value;
        }
    }

    $catalogBaseUrl = $mode === 'search'
        ? $localePrefix . '/search'
        : $localePrefix . '/category/' . ($currentCategory['url_path'] ?? $currentCategory['slug'] ?? '');
    $buildCatalogUrl = fn (array $params = []): string => ($params ? $catalogBaseUrl . '?' . http_build_query($params) : $catalogBaseUrl);
    $pageUrl = function (int $page) use ($baseParams, $buildCatalogUrl): string {
        $params = $baseParams;

        unset($params['page']);

        if ($page > 1) {
            $params['page'] = $page;
        }

        return $buildCatalogUrl($params);
    };
    $filterUrl = function (int $fieldId, string $value) use ($baseParams, $buildCatalogUrl): string {
        $params = $baseParams;
        unset($params['page']);
        $current = collect($params['f'][$fieldId] ?? [])->map(fn ($item) => (string) $item)->values();

        if ($current->contains($value)) {
            $next = $current->reject(fn ($item) => $item === $value)->values()->all();
        } else {
            $next = $current->push($value)->unique()->values()->all();
        }

        if ($next === []) {
            unset($params['f'][$fieldId]);
        } else {
            $params['f'][$fieldId] = $next;
        }

        if (($params['f'] ?? []) === []) {
            unset($params['f']);
        }

        return $buildCatalogUrl($params);
    };
    $clearFiltersUrl = function () use ($baseParams, $buildCatalogUrl): string {
        $params = $baseParams;
        unset($params['f'], $params['page']);

        return $buildCatalogUrl($params);
    };
@endphp
@include('storefront.partials.site-header')
<main>
    <div class="wrap">
        <section class="hero">
            <small>{{ $mode === 'search' ? ($copy['search'] ?? 'Search') : ($copy['category'] ?? 'Category') }}</small>
            <h1>{{ $title }}</h1>
            <p>{{ $subtitle }}</p>
        </section>
        <div class="layout">
            <aside class="sidebar">
                <h2 class="side-title">{{ $showSubcategoryTiles ? ($copy['sections'] ?? 'Sections') : ($copy['filters'] ?? 'Filters') }}</h2>

                @if($showSubcategoryTiles)
                    <div class="section-links" aria-label="Subcategories">
                        @foreach($subcategoryTiles as $subcategory)
                            <a class="section-link" href="{{ $categoryUrl($subcategory) }}">
                                <span>{{ $subcategory['name'] ?? 'Category' }}</span>
                                <em>›</em>
                            </a>
                        @endforeach
                    </div>
                @elseif($selectedFilters)
                    <a class="filter-clear" href="{{ $clearFiltersUrl() }}">Clear filters</a>

                    @if($extraFilters)
                        <div class="dynamic-filters" aria-label="Product filters">
                            @foreach($extraFilters as $filter)
                                @php($hasSelectedOption = collect($filter['values'])->contains(fn ($option) => $option['selected']))
                                <details class="filter-group" {{ $hasSelectedOption || $loop->first ? 'open' : '' }}>
                                    <summary>
                                        <span class="filter-group-head">
                                            <strong>{{ $filter['name'] }}</strong>
                                            @if($filter['section'])
                                                <small>{{ $filter['section'] }}</small>
                                            @endif
                                        </span>
                                    </summary>

                                    <div class="filter-options">
                                        @foreach($filter['values'] as $option)
                                            <a class="filter-option {{ $option['selected'] ? 'is-active' : '' }}" href="{{ $filterUrl((int) $filter['id'], $option['value']) }}">
                                                <span>{{ $option['value'] }}</span>
                                                <em>{{ number_format($option['count']) }}</em>
                                            </a>
                                        @endforeach
                                    </div>
                                </details>
                            @endforeach
                        </div>
                    @else
                        <div class="side-help">
                            <small>{{ $copy['buyer_mode'] ?? 'Buyer mode' }}</small>
                            <strong>No filters for this view yet.</strong>
                            <p></p>
                        </div>
                    @endif
                @elseif($extraFilters)
                    <div class="dynamic-filters" aria-label="Product filters">
                        @foreach($extraFilters as $filter)
                            @php($hasSelectedOption = collect($filter['values'])->contains(fn ($option) => $option['selected']))
                            <details class="filter-group" {{ $hasSelectedOption || $loop->first ? 'open' : '' }}>
                                <summary>
                                    <span class="filter-group-head">
                                        <strong>{{ $filter['name'] }}</strong>
                                        @if($filter['section'])
                                            <small>{{ $filter['section'] }}</small>
                                        @endif
                                    </span>
                                </summary>

                                <div class="filter-options">
                                    @foreach($filter['values'] as $option)
                                        <a class="filter-option {{ $option['selected'] ? 'is-active' : '' }}" href="{{ $filterUrl((int) $filter['id'], $option['value']) }}">
                                            <span>{{ $option['value'] }}</span>
                                            <em>{{ number_format($option['count']) }}</em>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                        @endforeach
                    </div>
                @else
                    <div class="side-help">
                        <small>{{ $copy['buyer_mode'] ?? 'Buyer mode' }}</small>
                        <strong>No filters for this view yet.</strong>
                        <p></p>
                    </div>
                @endif
            </aside>

            <section class="panel">
                @if($showSubcategoryTiles)
                    <div class="panel-head">
                        <div>
                            <strong>{{ $copy['sections'] ?? 'Sections' }}</strong>
                            <span>{{ count($subcategoryTiles) }} {{ $copy['sections_available'] ?? 'sections available' }}</span>
                        </div>
                    </div>

                    <div class="section-grid">
                        @foreach($subcategoryTiles as $subcategory)
                            <a class="section-tile" href="{{ $categoryUrl($subcategory) }}">
                                <span class="section-icon">{{ mb_substr($subcategory['name'] ?? 'C', 0, 1) }}</span>
                                <span>
                                    <strong>{{ $subcategory['name'] ?? 'Category' }}</strong>
                                    <span>{{ $copy['browse_products'] ?? 'Browse products' }}</span>
                                </span>
                                <span class="section-arrow">›</span>
                            </a>
                        @endforeach
                    </div>
                @elseif($products)
                    <div class="grid">
                        @foreach($products as $product)
                            @include('storefront.partials.product-card', ['product' => $product])
                        @endforeach
                    </div>

                    <div class="pager">
                        <span>{{ number_format($pagination['from']) }}-{{ number_format($pagination['to']) }} / {{ number_format($productTotal) }}</span>
                        <div class="pager-actions">
                            @if($pagination['hasPrevious'])
                                <a class="gallery-nav" href="{{ $pageUrl($pagination['page'] - 1) }}" aria-label="Previous page">‹</a>
                            @else
                                <span class="pager-nav-placeholder">‹</span>
                            @endif
                            <span class="pager-number">{{ $pagination['page'] }} / {{ $pagination['lastPage'] }}</span>
                            @if($pagination['hasNext'])
                                <a class="gallery-nav" href="{{ $pageUrl($pagination['page'] + 1) }}" aria-label="Next page">›</a>
                            @else
                                <span class="pager-nav-placeholder">›</span>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="empty">{{ $copy['no_products'] ?? 'No products found.' }}</div>
                @endif
            </section>
        </div>
    </div>
</main>
@include('storefront.partials.site-footer')
@include('storefront.partials.site-header-script')
</body>
</html>
