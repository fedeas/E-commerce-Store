<!DOCTYPE html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page['title'] ?? 'Information' }} | {{ config('app.name') }}</title>
    @include('storefront.partials.site-fonts')
    <style>
        :root { --ink:#20242c; --muted:#667085; --line:#d9e0e8; --soft:#f3f6f8; --dark:#18212c; --green:#00a6a6; --radius:8px; --shadow:0 18px 45px rgba(24,33,44,.12); }
        @include('storefront.partials.site-font-styles')
        * { box-sizing:border-box; }
        body { margin:0; font-family:var(--font-sans); background:#eef2f5; color:var(--ink); letter-spacing:0; }
        a { color:inherit; text-decoration:none; }
        button, input, select, textarea { font:inherit; letter-spacing:0; }
        button { cursor:pointer; }
        .wrap { width:min(1250px, calc(100% - 32px)); margin:0 auto; }
        main { padding:24px 0 56px; }
        .page-hero {
            padding:34px;
            border-radius:var(--radius);
            background:linear-gradient(135deg,#18212c 0%,#1f3645 58%,#0c7375 100%);
            color:#fff;
            box-shadow:var(--shadow);
        }
        .page-hero small { color:#7dd3fc; font-size:11px; font-weight:950; letter-spacing:1.5px; text-transform:uppercase; }
        .page-hero h1 { max-width:780px; margin:10px 0 0; font-size:42px; line-height:1.05; }
        .page-hero p { max-width:820px; margin:14px 0 0; color:#d8e5ee; font-size:15px; line-height:1.65; font-weight:750; }
        .content-layout { margin-top:18px; display:grid; grid-template-columns:260px minmax(0,1fr); gap:18px; align-items:start; }
        .content-nav, .content-card {
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
            box-shadow:0 10px 25px rgba(24,33,44,.05);
        }
        .content-nav { position:sticky; top:92px; padding:10px; display:grid; gap:6px; }
        .content-nav a {
            min-height:42px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:10px;
            border-radius:var(--radius);
            padding:0 12px;
            color:#34404a;
            font-size:12px;
            font-weight:950;
        }
        .content-nav a.is-active, .content-nav a:hover { background:#e9fbfb; color:#087782; }
        .content-card { padding:30px; overflow:hidden; }
        .content-body { max-width:920px; color:#34404a; font-size:15px; line-height:1.72; font-weight:700; }
        .content-body h2, .content-body h3, .content-body p:first-child strong {
            color:#18212c;
            line-height:1.15;
        }
        .content-body h2 { margin:0 0 14px; font-size:30px; }
        .content-body h3 { margin:28px 0 10px; font-size:20px; }
        .content-body p { margin:0 0 14px; }
        .content-body strong { color:#18212c; font-weight:950; }
        .content-body ul { margin:0 0 18px; padding-left:22px; }
        .content-body li { margin:8px 0; }
        .content-body hr { margin:24px 0; border:0; border-top:1px solid #e3e8ef; }
        .content-body a { color:#087782; font-weight:950; text-decoration:underline; text-underline-offset:3px; }
        .content-body table {
            width:100%;
            margin:14px 0 22px;
            border-collapse:collapse;
            border:1px solid #d9e0e8;
            border-radius:var(--radius);
            overflow:hidden;
            font-size:13px;
        }
        .content-body th, .content-body td { padding:12px; border:1px solid #d9e0e8; text-align:left; vertical-align:top; }
        .content-body th { background:#f3f8fb; color:#18212c; font-weight:950; }
        .content-body [class] { max-width:100%; }
        @media (max-width:920px) {
            .content-layout { grid-template-columns:1fr; }
            .content-nav { position:static; grid-template-columns:repeat(2,minmax(0,1fr)); }
        }
        @media (max-width:620px) {
            .wrap { width:min(100% - 20px, 1250px); }
            .page-hero { padding:24px; }
            .page-hero h1 { font-size:34px; }
            .content-nav { grid-template-columns:1fr; }
            .content-card { padding:20px; }
            .content-body table { display:block; overflow-x:auto; white-space:nowrap; }
        }
    </style>
    @include('storefront.partials.site-header-style-tag')
</head>
<body>
@include('storefront.partials.site-header')

<main>
    <div class="wrap">
        <section class="page-hero">
            <small>{{ $page['eyebrow'] ?? 'Information' }}</small>
            <h1>{{ $page['title'] ?? 'Information' }}</h1>
            <p>{{ $page['intro'] ?? '' }}</p>
        </section>

        <div class="content-layout">
            <aside class="content-nav" aria-label="Information pages">
                <a class="{{ ($page['slug'] ?? '') === 'shipping' ? 'is-active' : '' }}" href="{{ $shippingUrl ?? (($localePrefix ?? '/en') . '/shipping') }}">{{ $copy['footer_shipping'] ?? 'Shipping' }} <span>›</span></a>
                <a class="{{ ($page['slug'] ?? '') === 'faq' ? 'is-active' : '' }}" href="{{ $faqUrl ?? (($localePrefix ?? '/en') . '/faq') }}">{{ $copy['footer_faq'] ?? 'F.A.Q.' }} <span>›</span></a>
                <a class="{{ ($page['slug'] ?? '') === 'cookie-policy' ? 'is-active' : '' }}" href="{{ $cookiePolicyUrl ?? (($localePrefix ?? '/en') . '/cookie-policy') }}">{{ $copy['footer_cookie'] ?? 'Cookie Policy' }} <span>›</span></a>
                <a class="{{ ($page['slug'] ?? '') === 'privacy-policy' ? 'is-active' : '' }}" href="{{ $privacyPolicyUrl ?? (($localePrefix ?? '/en') . '/privacy-policy') }}">{{ $copy['footer_privacy'] ?? 'Privacy Policy' }} <span>›</span></a>
                <a class="{{ ($page['slug'] ?? '') === 'terms-and-conditions' ? 'is-active' : '' }}" href="{{ $termsUrl ?? (($localePrefix ?? '/en') . '/terms-and-conditions') }}">{{ $copy['footer_terms'] ?? 'Terms and Conditions' }} <span>›</span></a>
            </aside>

            <article class="content-card">
                <div class="content-body">
                    @if(($locale ?? 'en') === 'el' && view()->exists('storefront.pages.el.' . ($page['slug'] ?? 'shipping')))
                        @include('storefront.pages.el.' . ($page['slug'] ?? 'shipping'))
                    @else
                        @includeIf('storefront.pages.' . ($page['slug'] ?? 'shipping'))
                    @endif
                </div>
            </article>
        </div>
    </div>
</main>

@include('storefront.partials.site-footer')
@include('storefront.partials.site-header-script')
</body>
</html>
