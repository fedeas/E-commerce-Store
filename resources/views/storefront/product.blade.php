<!DOCTYPE html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $product['title'] ?? 'Product' }} | {{ config('app.name') }}</title>
    @include('storefront.partials.site-fonts')
    <style>
        :root { --ink:#20242c; --muted:#667085; --line:#d9e0e8; --soft:#f3f6f8; --dark:#18212c; --green:#00a6a6; --green-2:#0d6f7f; --blue:#2f6fed; --red:#c2413a; --radius:8px; }
        @include('storefront.partials.site-font-styles')
        * { box-sizing:border-box; }
        body { margin:0; font-family:var(--font-sans); background:#eef2f5; color:var(--ink); letter-spacing:0; }
        a { color:inherit; text-decoration:none; }
        img { display:block; max-width:100%; }
        .wrap { width:min(1180px, calc(100% - 32px)); margin:0 auto; }
        .top { background:#18212c; color:#fff; border-bottom:1px solid rgba(0,166,166,.55); }
        .top .wrap { min-height:31px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }
        .header { background:#fff; border-bottom:1px solid var(--line); }
        .header .wrap { min-height:70px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .brand { display:flex; align-items:center; gap:10px; min-width:0; }
        .brand strong { display:block; font-size:20px; line-height:1; font-weight:900; }
        .brand span span { display:block; margin-top:4px; color:var(--muted); font-size:11px; font-weight:900; text-transform:uppercase; }
        .header-actions { display:flex; gap:8px; }
        .header-actions a { min-height:42px; display:inline-flex; align-items:center; padding:0 12px; border:1px solid var(--line); border-radius:var(--radius); font-size:12px; font-weight:900; }
        .cart-link { background:var(--dark); color:#fff; border-color:var(--dark) !important; }
        main { padding:26px 0 52px; }
        .product { display:grid; grid-template-columns:minmax(320px, 520px) minmax(0, 1fr); gap:22px; align-items:start; }
        .gallery, .summary, .info-card { border:1px solid var(--line); border-radius:var(--radius); background:#fff; box-shadow:none; }
        .gallery { min-height:520px; padding:26px; display:grid; place-items:center; background:linear-gradient(135deg,#fff 0%,#f3f6f8 55%,#e9eef5 100%); }
        .gallery img { width:100%; height:100%; max-height:460px; object-fit:contain; }
        .product-placeholder { width:74%; aspect-ratio:1; display:grid; place-items:center; align-content:center; gap:8px; border:1px dashed #cfd9e2; border-radius:var(--radius); background:#fff; color:#6c7783; text-align:center; }
        .product-placeholder strong { width:70px; height:70px; display:grid; place-items:center; border-radius:18px; background:#eaf1ff; color:#2f6fed; font-size:22px; font-weight:900; }
        .product-placeholder span { font-size:11px; font-weight:900; text-transform:uppercase; }
        .gallery-stack { display:grid; gap:12px; }
        .thumbs { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:8px; }
        .thumbs button { padding:0; border:1px solid var(--line); border-radius:var(--radius); background:#fff; cursor:pointer; aspect-ratio:1; overflow:hidden; }
        .thumbs img { width:100%; height:100%; object-fit:contain; }
        .summary { padding:26px; }
        .crumb { color:var(--muted); font-size:12px; font-weight:900; text-transform:uppercase; }
        h1 { margin:12px 0 12px; font-size:34px; line-height:1.12; }
        .meta { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:18px; }
        .meta span { min-height:28px; display:inline-flex; align-items:center; padding:0 9px; border-radius:20px; background:#eaf1ff; color:#244268; font-size:11px; font-weight:900; }
        .price-box { padding:18px; border-radius:var(--radius); background:#f7f9fb; border:1px solid var(--line); }
        .price { display:flex; align-items:baseline; gap:10px; }
        .price strong { color:var(--red); font-size:32px; line-height:1; }
        .price span { color:#8a96a2; font-size:16px; font-weight:800; text-decoration:line-through; }
        .vat { margin-top:6px; color:var(--muted); font-size:12px; font-weight:700; }
        .actions { display:flex; gap:10px; margin-top:18px; }
        .btn { min-height:46px; display:inline-flex; align-items:center; justify-content:center; padding:0 18px; border-radius:var(--radius); background:#00a6a6; color:#fff; font:inherit; font-size:13px; font-weight:900; border:0; cursor:pointer; }
        .btn.secondary { background:var(--dark); }
        .desc { margin-top:20px; color:#4f5964; font-size:14px; line-height:1.65; font-weight:600; white-space:pre-line; }
        .spec-strip { margin-top:18px; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
        .spec-pill { min-height:64px; padding:12px; border:1px solid var(--line); border-radius:var(--radius); background:#fff; }
        .spec-pill small { color:#7a8996; font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:1px; }
        .spec-pill strong { display:block; margin-top:6px; font-size:13px; line-height:1.25; }
        .info-grid { margin-top:18px; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .info-card { padding:16px; }
        .info-card strong { display:block; font-size:13px; }
        .info-card span { display:block; margin-top:5px; color:var(--muted); font-size:12px; font-weight:700; }
        .specs-panel { margin-top:18px; border:1px solid var(--line); border-radius:var(--radius); background:#fff; overflow:hidden; }
        .specs-head { padding:16px 18px; border-bottom:1px solid var(--line); }
        .specs-head strong { display:block; font-size:16px; }
        .specs-table { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); }
        .spec-row { display:grid; grid-template-columns:minmax(130px, .85fr) minmax(0, 1fr); border-bottom:1px solid #edf1f5; }
        .spec-row:nth-child(odd) { border-right:1px solid #edf1f5; }
        .spec-row span, .spec-row strong { padding:10px 12px; font-size:12px; line-height:1.35; }
        .spec-row span { color:#5d6b7a; background:#f8fafc; font-weight:900; }
        .spec-row strong { color:#1f2937; font-weight:800; }
        @media (max-width:880px) { .product { grid-template-columns:1fr; } .gallery { min-height:360px; } .spec-strip, .info-grid { grid-template-columns:1fr; } h1 { font-size:28px; } }
        @media (max-width:720px) { .specs-table { grid-template-columns:1fr; } .spec-row:nth-child(odd) { border-right:0; } }
        @media (max-width:520px) { .wrap { width:min(100% - 20px, 1180px); } .header .wrap { display:grid; grid-template-columns:1fr; padding:12px 0; } .actions { display:grid; } }
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
@endphp
@include('storefront.partials.site-header')
<main>
    <div class="wrap">
        <section class="product">
            <div class="gallery-stack">
                <div class="gallery">
                    @if($product['image'] ?? '')
                        <img id="product-main-image" src="{{ $product['image'] }}" alt="{{ $product['title'] }}">
                    @else
                        <div class="product-placeholder" aria-hidden="true">
                            <strong>CL</strong>
                            <span>{{ $copy['image_pending'] ?? 'Image pending' }}</span>
                        </div>
                    @endif
                </div>

                @if(count($product['images'] ?? []) > 1)
                    <div class="thumbs" aria-label="Product images">
                        @foreach($product['images'] as $image)
                            <button type="button" onclick="document.getElementById('product-main-image').src='{{ $image }}'">
                                <img src="{{ $image }}" alt="">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="summary">
                <div class="crumb">{{ $product['category'] ?: ($copy['product'] ?? 'Product') }} {{ $product['brand'] ? ' / ' . $product['brand'] : '' }}</div>
                <h1>{{ $product['title'] }}</h1>
                <div class="meta">
                    <span>Code: {{ ($product['display_code'] ?? '') ?: ($copy['on_request'] ?? 'On request') }}</span>
                    @if($product['mpn'] ?? '')<span>MPN: {{ $product['mpn'] }}</span>@endif
                    @if($product['ean'] ?? '')<span>EAN: {{ $product['ean'] }}</span>@endif
                    <span>{{ $product['label'] ?? ($copy['available'] ?? 'Available') }}</span>
                </div>

                <div class="price-box">
                    <div class="price">
                        <strong>{{ $product['price'] }}</strong>
                        @if($product['old_price'] ?? '')<span>{{ $product['old_price'] }}</span>@endif
                    </div>
                    <div class="vat">{{ $product['price_note'] ?? ($copy['tax_note_retail'] ?? 'VAT included where available.') }}</div>
                </div>

                <div class="actions">
                    <form method="post" action="{{ $cartAddUrl ?? ($localePrefix . '/cart/items') }}">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product['id'] ?? 0 }}">
                        <input type="hidden" name="quantity" value="1">
                        <button class="btn" type="submit">{{ $copy['add_to_cart'] ?? 'Add to cart' }}</button>
                    </form>
                    <a class="btn secondary" href="{{ $searchUrl }}?q={{ urlencode($product['code'] ?? ($product['display_code'] ?? '')) }}">{{ $copy['find_similar'] ?? 'Find similar' }}</a>
                </div>

                <p class="desc">{{ $product['description'] ?: 'This live catalog item does not include a long description yet.' }}</p>

                <div class="spec-strip" aria-label="Product identifiers">
                    <div class="spec-pill"><small>{{ $copy['brand'] ?? 'Brand' }}</small><strong>{{ $product['brand'] ?: ($copy['on_request'] ?? 'On request') }}</strong></div>
                    <div class="spec-pill"><small>MPN</small><strong>{{ $product['mpn'] ?: ($copy['on_request'] ?? 'On request') }}</strong></div>
                    <div class="spec-pill"><small>EAN</small><strong>{{ $product['ean'] ?: ($copy['on_request'] ?? 'On request') }}</strong></div>
                </div>
            </div>
        </section>

        @if(!empty($product['specs_table']))
            <section class="specs-panel">
                <div class="specs-head">
                    <strong>{{ $copy['technical_specs'] ?? 'Technical specs' }}</strong>
                </div>
                <div class="specs-table">
                    @foreach($product['specs_table'] as $spec)
                        <div class="spec-row">
                            <span>{{ $spec['name'] }}</span>
                            <strong>{{ $spec['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="info-grid">
            <div class="info-card"><strong>EU stock</strong><span>Ready for stock and supplier availability integration.</span></div>
            <div class="info-card"><strong>B2B pricing</strong><span>Account-based pricing can be shown after sign-in.</span></div>
            <div class="info-card"><strong>Support</strong><span>Use this page for product details, specs and recommendations.</span></div>
        </section>
    </div>
</main>
@include('storefront.partials.site-footer')
@include('storefront.partials.site-header-script')
</body>
</html>
