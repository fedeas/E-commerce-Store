@php
    $copy = $copy ?? [];
    $locale = $locale ?? 'en';
    $localePrefix = '/' . $locale;
    $homeUrl = $homeUrl ?? $localePrefix;
    $searchUrl = $searchUrl ?? ($localePrefix . '/search');
    $categoryUrl = $categoryUrl ?? fn (array $category): string => $localePrefix . '/category/' . ($category['url_path'] ?? $category['slug'] ?? '');
    $drawerCategories = $drawerCategories ?? array_slice($categories ?? [], 0, 12);
    $loginUrl = $loginUrl ?? ($localePrefix . '/login');
    $registerUrl = $registerUrl ?? ($localePrefix . '/register');
    $logoutUrl = $logoutUrl ?? ($localePrefix . '/logout');
    $b2bRequestUrl = $b2bRequestUrl ?? ($localePrefix . '/b2b-request');
    $contactUrl = $contactUrl ?? ($localePrefix . '/contact');
    $brandLogoUrl = asset('images/brand/logo.svg');
@endphp

<header class="site-header">
    <div class="wrap">
        <div class="header-main">
            <button class="products-trigger js-open-catalog" type="button">
                <span class="hamb" aria-hidden="true"><span></span><span></span><span></span></span>
                {{ $copy['category'] ?? 'Catalog' }}
            </button>

            <a href="{{ $homeUrl }}" class="brand" aria-label="Home">
                <span class="brand-logo">
                    <img src="{{ $brandLogoUrl }}" alt="" width="170" height="50">
                </span>
                <span class="sr-only">B2B IT Solutions</span>
            </a>

            <form class="search-box" action="{{ $searchUrl }}" method="get">
                <input type="search" name="q" value="{{ $query ?? '' }}" placeholder="{{ $copy['search_placeholder'] ?? 'Search products, SKU, brand...' }}">
                <button type="submit" aria-label="{{ $copy['search'] ?? 'Search' }}">⌕</button>
            </form>

            <div class="header-actions">
                <div class="call-wrap">
                    <a class="icon-btn" href="{{ $contactUrl }}">{{ $copy['contact'] ?? 'Contact' }}</a>
                </div>
                <div class="seg-control" aria-label="{{ $copy['language'] ?? 'Language' }}">
                    <a href="{{ $languageUrls['en'] ?? '/en' }}" class="{{ $locale === 'en' ? 'is-active' : '' }}">EN</a>
                    <a href="{{ $languageUrls['el'] ?? '/el' }}" class="{{ $locale === 'el' ? 'is-active' : '' }}">EL</a>
                </div>
                @auth
                    <div class="account-wrap">
                        <button class="icon-btn account-btn" type="button">
                            <span class="account-mini">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                            <span>{{ auth()->user()->name }}</span>
                        </button>
                        <div class="account-drop">
                            <small>{{ $copy['account'] ?? 'Account' }}</small>
                            <strong>{{ auth()->user()->name }}</strong>
                            <span>{{ auth()->user()->email }}</span>
                            <span>{{ $copy['b2b_status'] ?? 'Pricing status' }}: {{ auth()->user()->isB2b() ? ($copy['b2b_active'] ?? 'B2B active') : (auth()->user()->hasPendingB2bRequest() ? ($copy['b2b_pending'] ?? 'B2B pending') : ($copy['retail'] ?? 'Retail')) }}</span>
                            @unless(auth()->user()->isB2b())
                                <a href="{{ $b2bRequestUrl }}">{{ auth()->user()->hasPendingB2bRequest() ? ($copy['b2b_pending'] ?? 'B2B pending') : ($copy['b2b_request'] ?? 'Request B2B') }}</a>
                            @endunless
                            <form method="POST" action="{{ $logoutUrl }}">
                                @csrf
                                <button type="submit">{{ $copy['logout'] ?? 'Logout' }}</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="seg-control auth-seg" aria-label="{{ $copy['account'] ?? 'Account' }}">
                        <a href="{{ $loginUrl }}">{{ $copy['sign_in'] ?? 'Sign in' }}</a>
                        <a href="{{ $registerUrl }}">{{ $copy['register'] ?? 'Register' }}</a>
                    </div>
                @endauth
                <a href="{{ $cartUrl ?? ($localePrefix . '/cart') }}" class="icon-btn cart-btn">{{ $copy['cart'] ?? 'Cart' }} <em>{{ $cartCount ?? 0 }}</em></a>
            </div>
        </div>
    </div>
</header>

<x-storefront.catalog-drawer
    :drawer-categories="$drawerCategories"
    :category-url="$categoryUrl"
    :copy="$copy"
    :home-url="$homeUrl"
/>
