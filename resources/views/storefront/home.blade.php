<!DOCTYPE html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    @include('storefront.partials.site-fonts')
    <style>
        :root {
            --ink:#20242c;
            --muted:#667085;
            --line:#d9e0e8;
            --soft:#f3f6f8;
            --panel:#ffffff;
            --dark:#18212c;
            --dark-2:#243241;
            --green:#00a6a6;
            --green-2:#0d6f7f;
            --orange:#f59e0b;
            --red:#c2413a;
            --blue:#2f6fed;
            --shadow:0 18px 44px rgba(24,33,44,.14);
            --shadow-soft:0 10px 24px rgba(24,33,44,.08);
            --radius:8px;
        }
        @include('storefront.partials.site-font-styles')

        * { box-sizing:border-box; }
        html { scroll-behavior:smooth; }

        body {
            margin:0;
            font-family:var(--font-sans);
            background:#eef2f5;
            color:var(--ink);
            letter-spacing:0;
        }

        a { color:inherit; text-decoration:none; }
        img, video { display:block; max-width:100%; }
        button, input, select, textarea { font:inherit; letter-spacing:0; }
        button { cursor:pointer; }

        .wrap {
            width:min(1250px, calc(100% - 32px));
            margin:0 auto;
        }

        main { padding:18px 0 50px; }

        .banner-carousel {
            position:relative;
            margin-top:0;
        }

        .home-banner {
            min-height:430px;
            position:relative;
            overflow:hidden;
            display:none;
            grid-template-columns:minmax(0, 1fr) 560px;
            align-items:stretch;
            border-radius:var(--radius);
            background:#18212c;
            color:#fff;
            box-shadow:var(--shadow-soft);
        }

        .home-banner.is-active { display:grid; }

        .home-banner.is-full-media {
            min-height:430px;
            background:#18212c;
        }

        .home-banner.is-active.is-full-media { display:block; }

        .banner-bg,
        .banner-bg img,
        .banner-bg video {
            position:absolute;
            inset:0;
            width:100%;
            height:100%;
            object-fit:cover;
        }

        .banner-bg:after {
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(90deg, rgba(24,33,44,.98), rgba(24,33,44,.84) 45%, rgba(24,33,44,.42));
        }

        .home-banner.is-full-media .banner-bg:after {
            background:linear-gradient(90deg, rgba(24,33,44,.88), rgba(24,33,44,.58) 48%, rgba(24,33,44,.20));
        }

        .banner-copy {
            position:relative;
            z-index:1;
            padding:34px 38px;
            display:flex;
            flex-direction:column;
            justify-content:center;
        }

        .home-banner.is-full-media .banner-copy {
            min-height:430px;
            width:min(680px, 62%);
        }

        .eyebrow {
            margin-bottom:9px;
            color:#7dd3fc;
            font-size:11px;
            font-weight:900;
            letter-spacing:1.7px;
            text-transform:uppercase;
        }

        .banner-copy h1 {
            max-width:580px;
            margin:0;
            font-size:42px;
            line-height:1.08;
            font-weight:900;
            text-shadow:0 3px 18px rgba(0,0,0,.35);
        }

        .banner-copy p {
            max-width:590px;
            margin:10px 0 0;
            color:#e7edf2;
            font-size:15px;
            line-height:1.55;
            font-weight:600;
        }

        .banner-copy .btn {
            width:max-content;
            margin-top:20px;
            background:#2f6fed;
        }

        .banner-products {
            position:relative;
            z-index:1;
            padding:24px;
            display:grid;
            grid-template-columns:1fr;
            gap:12px;
            align-items:center;
            align-content:center;
        }

        .hero-product {
            min-width:0;
            min-height:118px;
            display:grid;
            grid-template-columns:138px minmax(0, 1fr);
            border-radius:var(--radius);
            background:#fff;
            color:var(--ink);
            overflow:hidden;
            box-shadow:0 6px 20px rgba(0,0,0,.16);
        }

        .hero-product .product-media {
            position:relative;
            min-height:118px;
            padding:0;
            display:grid;
            place-items:center;
            background:#fff;
            border-right:0;
        }

        .hero-product .product-media a {
            width:100%;
            height:100%;
            display:grid;
            place-items:center;
        }

        .hero-product .product-media img {
            width:75%;
            height:100%;
            object-fit:contain;
        }

        .hero-product .product-media > span:not(.product-label) {
            width:100%;
            height:100%;
            display:grid;
            place-items:center;
            border-radius:0;
            background:transparent;
        }

        .hero-product .product-label {
            position:absolute;
            top:8px;
            left:8px;
            z-index:2;
            min-height:22px;
            display:inline-flex;
            align-items:center;
            padding:0 8px;
            border-radius:20px;
            background:#eaf1ff;
            color:#2f6fed;
            font-size:10px;
            font-weight:900;
        }

        .tile-visual span,
        .deal-img span {
            width:64%;
            height:64%;
            display:block;
            border-radius:var(--radius);
            background:
                linear-gradient(135deg, rgba(47,111,237,.16), transparent 55%),
                linear-gradient(145deg, #edf1f4, #dfe5ea);
        }

        .hero-product-body {
            padding:14px 14px 12px;
            display:flex;
            flex:1;
            flex-direction:column;
            gap:7px;
            min-width:0;
            margin-left:1rem;   
        }

        .hero-product-body strong {
            display:-webkit-box;
            -webkit-line-clamp:3;
            line-clamp:3;
            -webkit-box-orient:vertical;
            overflow:hidden;
            font-size:13px;
            line-height:1.35;
        }

        .hero-price {
            margin-top:auto;
            color:var(--red);
            font-size:15px;
            font-weight:900;
        }

        .banner-controls,
        .gallery-controls {
            position:absolute;
            right:18px;
            bottom:18px;
            z-index:5;
            display:flex;
            align-items:center;
            gap:8px;
        }

        .banner-dot,
        .gallery-nav,
        .gallery-dot {
            border:1px solid rgba(255,255,255,.34);
            background:rgba(24,33,44,.64);
            color:#fff;
            cursor:pointer;
        }

        .gallery-nav {
            width:38px;
            height:38px;
            display:grid;
            place-items:center;
            border-radius:var(--radius);
            font-size:18px;
            font-weight:900;
        }

        .banner-dots,
        .gallery-dots { display:flex; gap:6px; }

        .banner-dot,
        .gallery-dot {
            width:9px;
            height:9px;
            padding:0;
            border-radius:50%;
        }

        .banner-dot.is-active,
        .gallery-dot.is-active { background:#00a6a6; border-color:#00a6a6; }

        .category-tiles {
            margin-top:20px;
            display:grid;
            grid-template-columns:repeat(4, minmax(0, 1fr));
            gap:14px;
        }

        .category-tile,
        .section-card,
        .product-card,
        .deal-card {
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
            box-shadow:0 5px 16px rgba(15,23,42,.04);
        }

        .category-tile {
            overflow:hidden;
            transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }

        .category-tile:hover {
            transform:translateY(-4px);
            border-color:#c8cdd4;
            box-shadow:var(--shadow-soft);
        }

        .tile-visual {
            aspect-ratio:4 / 3;
            display:grid;
            place-items:center;
            padding:12px;
            background:linear-gradient(180deg, #f7f7f9, #f1f3f5);
        }

        .tile-visual .cat-icon {
            width:92px;
            height:92px;
            display:grid;
            place-items:center;
            border-radius:22px;
            background:#111820;
            color:#fff;
        }

        .tile-visual .cat-icon svg {
            width:54px;
            height:54px;
            stroke:currentColor;
            stroke-width:1.9;
            stroke-linecap:round;
            stroke-linejoin:round;
            fill:none;
        }

        .tile-bar {
            min-height:46px;
            padding:10px 13px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:8px;
            background:#18212c;
            color:#fff;
        }

        .tile-bar strong {
            min-width:0;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
            font-size:12px;
            font-weight:900;
            text-transform:uppercase;
        }

        .tile-bar span { color:rgba(255,255,255,.75); font-weight:900; }

        .section-card {
            margin-top:20px;
            padding:16px;
        }

        .section-head {
            min-height:44px;
            margin-bottom:14px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            padding:0 2px;
        }

        .section-title {
            min-height:42px;
            padding:0 16px;
            display:inline-flex;
            align-items:center;
            border-radius:var(--radius);
            background:#18212c;
            color:#fff;
            font-size:13px;
            font-weight:900;
            text-transform:uppercase;
            letter-spacing:.6px;
        }

        .section-head p {
            margin:0;
            color:var(--muted);
            font-size:13px;
            font-weight:700;
        }

        .brands-marquee {
            margin-top:20px;
            overflow:hidden;
            border-top:1px solid #e9edf1;
            border-bottom:1px solid #e9edf1;
            background:#f8f8f8;
            position:relative;
        }

        .brands-marquee:before,
        .brands-marquee:after {
            content:"";
            position:absolute;
            top:0;
            z-index:2;
            width:80px;
            height:100%;
        }

        .brands-marquee:before { left:0; background:linear-gradient(90deg, #f8f8f8, transparent); }
        .brands-marquee:after { right:0; background:linear-gradient(270deg, #f8f8f8, transparent); }

        .brands-track {
            width:max-content;
            display:flex;
            animation:brandScroll 34s linear infinite;
        }

        .brands-marquee:hover .brands-track { animation-play-state:paused; }

        @keyframes brandScroll {
            from { transform:translateX(0); }
            to { transform:translateX(-50%); }
        }

        .brand-name {
            padding:18px 28px;
            color:#a5adb5;
            font-size:15px;
            font-weight:900;
            white-space:nowrap;
        }

        .brand-name:hover { color:var(--ink); }

        .catalog-intel {
            margin-top:20px;
            display:grid;
            grid-template-columns:1.15fr .85fr;
            gap:14px;
        }

        .intel-panel {
            min-width:0;
            padding:20px;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
        }

        .intel-panel.dark {
            display:grid;
            align-content:space-between;
            gap:18px;
            background:#18212c;
            color:#fff;
            border-color:#18212c;
        }

        .intel-kicker {
            color:#7dd3fc;
            font-size:11px;
            font-weight:900;
            letter-spacing:1.6px;
            text-transform:uppercase;
        }

        .intel-panel h2 {
            margin:8px 0 8px;
            font-size:26px;
            line-height:1.16;
        }

        .intel-panel p {
            margin:0;
            color:#c5ced8;
            font-size:14px;
            line-height:1.65;
            font-weight:650;
        }

        .quick-searches {
            display:flex;
            flex-wrap:wrap;
            gap:8px;
        }

        .quick-searches a {
            min-height:34px;
            display:inline-flex;
            align-items:center;
            padding:0 11px;
            border-radius:var(--radius);
            background:#243241;
            color:#e9f2fb;
            font-size:12px;
            font-weight:900;
        }

        .stat-wall {
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap:10px;
        }

        .stat-tile {
            min-height:94px;
            padding:15px;
            border:1px solid #e1e8ef;
            border-radius:var(--radius);
            background:#f9fbfd;
        }

        .stat-tile strong {
            display:block;
            font-size:26px;
            line-height:1;
        }

        .stat-tile span {
            display:block;
            margin-top:8px;
            color:#657385;
            font-size:12px;
            font-weight:850;
        }

        .workspace-grid {
            display:grid;
            grid-template-columns:repeat(3, minmax(0, 1fr));
            gap:14px;
        }

        .service-grid {
            display:grid;
            grid-template-columns:repeat(4, minmax(0, 1fr));
            gap:12px;
        }

        .service-card {
            min-height:150px;
            padding:18px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#f9fbfd;
        }

        .service-card b {
            width:42px;
            height:42px;
            display:grid;
            place-items:center;
            border-radius:12px;
            background:#18212c;
            color:#7dd3fc;
            font-size:15px;
        }

        .service-card strong {
            display:block;
            margin-top:14px;
            font-size:16px;
        }

        .service-card span {
            display:block;
            margin-top:8px;
            color:var(--muted);
            font-size:12px;
            line-height:1.5;
            font-weight:750;
        }

        .workspace-card {
            min-height:150px;
            padding:18px;
            display:flex;
            flex-direction:column;
            justify-content:space-between;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
        }

        .workspace-card strong {
            display:block;
            font-size:17px;
            line-height:1.2;
        }

        .workspace-card span {
            display:block;
            margin-top:8px;
            color:var(--muted);
            font-size:12px;
            line-height:1.5;
            font-weight:750;
        }

        .workspace-card em {
            color:#2f6fed;
            font-style:normal;
            font-size:12px;
            font-weight:900;
        }

        .assistant-card {
            margin-top:20px;
            display:grid;
            grid-template-columns:minmax(0, 1fr) 420px;
            gap:28px;
            padding:34px 38px;
            border-radius:var(--radius);
            background:#18212c;
            color:#fff;
        }

        .assistant-copy small {
            color:#7dd3fc;
            font-size:11px;
            font-weight:900;
            letter-spacing:1.8px;
            text-transform:uppercase;
        }

        .assistant-copy h2 {
            margin:10px 0 10px;
            max-width:520px;
            font-size:30px;
            line-height:1.15;
        }

        .assistant-copy p {
            max-width:620px;
            margin:0;
            color:#b6bdc6;
            font-size:14px;
            line-height:1.65;
            font-weight:600;
        }

        .assistant-panel {
            padding:18px;
            border:1px solid #343b45;
            border-radius:var(--radius);
            background:#243241;
        }

        .assistant-panel label {
            display:block;
            margin-bottom:8px;
            color:#d9dee5;
            font-size:12px;
            font-weight:800;
        }

        .assistant-options {
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap:8px;
        }

        .assistant-options a {
            min-height:54px;
            padding:10px;
            display:flex;
            flex-direction:column;
            justify-content:center;
            border:1px solid #353d47;
            border-radius:var(--radius);
            background:#18212c;
        }

        .assistant-options strong {
            color:#fff;
            font-size:12px;
        }

        .assistant-options span {
            margin-top:3px;
            color:#8f98a3;
            font-size:11px;
            font-weight:700;
        }

        .assistant-options a:hover { border-color:#7dd3fc; background:#102532; }

        .deal-grid,
        .product-grid {
            display:grid;
            grid-template-columns:repeat(5, minmax(0, 1fr));
            gap:14px;
        }

        .deal-card {
            position:relative;
            overflow:hidden;
            display:flex;
            flex-direction:column;
            min-width:0;
            height:100%;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
            transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .deal-card:hover {
            transform:translateY(-2px);
            border-color:#aebfd2;
            box-shadow:0 14px 28px rgba(24,33,44,.10);
        }

        .deal-badge {
            position:absolute;
            top:10px;
            right:10px;
            z-index:2;
            min-height:24px;
            display:inline-flex;
            align-items:center;
            padding:0 8px;
            border-radius:20px;
            background:#ffe6e6;
            color:var(--red);
            font-size:10px;
            font-weight:900;
        }

        .deal-img {
            position:relative;
            aspect-ratio:4 / 3;
            display:grid;
            place-items:center;
            padding:18px;
            background:linear-gradient(135deg,#fff 0%,#f3f6f8 55%,#e9eef5 100%);
            border-bottom:1px solid var(--line);
        }

        .deal-img img {
            width:100%;
            height:100%;
            object-fit:contain;
        }

        .deal-bar {
            padding:14px;
            display:flex;
            flex:1;
            flex-direction:column;
            gap:10px;
            background:#fff;
            color:var(--ink);
        }

        .deal-bar strong {
            margin:0;
            display:block;
            overflow:hidden;
            font-size:13px;
            line-height:1.45;
            overflow-wrap:anywhere;
        }

        .deal-price {
            margin-top:auto;
            display:flex;
            gap:7px;
            align-items:baseline;
        }

        .deal-price b { color:var(--red); font-size:18px; line-height:1.1; }
        .deal-price span { color:#8b98a3; font-size:12px; text-decoration:line-through; font-weight:800; }

        .product-grid { grid-template-columns:repeat(4, minmax(0, 1fr)); }

        .product-carousel {
            position:relative;
            overflow:hidden;
            margin-top:2px;
            padding-bottom:54px;
        }

        .product-carousel-viewport {
            overflow:hidden;
            margin:0 -1px;
            padding:1px;
        }

        .product-carousel-track {
            display:flex;
            gap:14px;
            transition:transform .36s ease;
            will-change:transform;
        }

        .product-carousel-track > .product-card {
            flex:0 0 calc((100% - 42px) / 4);
        }

        .gallery-controls {
            right:0;
            bottom:0;
        }

        .gallery-nav,
        .gallery-dot {
            border-color:#cbd6e2;
            background:#fff;
            color:#18212c;
            box-shadow:0 6px 16px rgba(24,33,44,.08);
        }

        .gallery-dot.is-active {
            background:#18212c;
            border-color:#18212c;
        }

        .product-card {
            display:flex;
            flex-direction:column;
            min-width:0;
            overflow:hidden;
            transition:transform .16s ease, box-shadow .16s ease, border-color .16s ease;
        }

        .product-card:hover {
            transform:translateY(-2px);
            border-color:#c8cdd4;
            box-shadow:var(--shadow-soft);
        }

        .product-media {
            position:relative;
            aspect-ratio:4 / 3;
            padding:18px;
            display:grid;
            place-items:center;
            background:#f8fafb;
            border-bottom:1px solid var(--line);
        }

        .product-media a {
            width:100%;
            height:100%;
            display:grid;
            place-items:center;
        }

        .product-media img {
            width:100%;
            height:100%;
            object-fit:contain;
        }

        .product-label,
        .product-save {
            position:absolute;
            top:10px;
            min-height:24px;
            display:inline-flex;
            align-items:center;
            padding:0 8px;
            border-radius:20px;
            font-size:10px;
            font-weight:900;
        }

        .product-label {
            left:10px;
            background:#eaf1ff;
            color:#2f6fed;
        }

        .product-save { right:10px; background:#ffe6e6; color:var(--red); }

        .product-placeholder {
            width:100%;
            height:100%;
            min-height:112px;
            display:grid;
            place-items:center;
            align-content:center;
            gap:6px;
            border:1px dashed #cfd9e2;
            border-radius:var(--radius);
            background:#fff;
            color:#6c7783;
            text-align:center;
        }

        .product-placeholder strong {
            width:54px;
            height:54px;
            display:grid;
            place-items:center;
            border-radius:14px;
            background:#eaf1ff;
            color:#2f6fed;
            font-size:18px;
            font-weight:900;
        }

        .product-placeholder span {
            font-size:10px;
            font-weight:900;
            text-transform:uppercase;
        }

        .product-body {
            padding:14px;
            display:flex;
            flex:1;
            flex-direction:column;
            gap:10px;
        }

        .sku-row {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:8px;
            color:#7a8996;
            font-size:10px;
            font-weight:900;
            text-transform:uppercase;
        }

        .sku {
            min-width:0;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }

        .stock-dot {
            color:#0d6f7f;
            white-space:nowrap;
        }

        .product-title {
            margin:0;
            display:block;
            overflow:visible;
            font-size:13px;
            line-height:1.45;
            overflow-wrap:anywhere;
        }

        .product-meta {
            min-height:26px;
            display:flex;
            flex-wrap:wrap;
            gap:5px;
        }

        .product-meta span {
            max-width:100%;
            min-height:22px;
            display:inline-flex;
            align-items:center;
            padding:0 7px;
            border-radius:999px;
            background:#f0f4f7;
            color:#637180;
            font-size:10px;
            font-weight:850;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
        }

        .price-row {
            margin-top:auto;
            display:flex;
            align-items:end;
            justify-content:space-between;
            gap:12px;
        }

        .price strong {
            display:block;
            color:var(--red);
            font-size:18px;
            line-height:1.1;
        }

        .price span {
            display:block;
            min-height:16px;
            margin-top:3px;
            color:#8b98a3;
            font-size:12px;
            font-weight:800;
            text-decoration:line-through;
        }

        .price .price-note {
            display:block;
            max-width:130px;
            margin-top:4px;
            color:#7a8996;
            font-size:10px;
            line-height:1.25;
            font-weight:850;
        }

        .add-btn {
            min-width:48px;
            height:34px;
            display:grid;
            place-items:center;
            border:0;
            border-radius:var(--radius);
            background:#00a6a6;
            color:#fff;
            padding:0 10px;
            font:inherit;
            font-size:11px;
            font-weight:900;
            cursor:pointer;
        }

        .business-card {
            margin-top:20px;
            padding:34px 0;
            display:grid;
            grid-template-columns:minmax(0, 1fr) minmax(320px, 480px);
            gap:42px;
            align-items:center;
        }

        .business-copy small {
            color:#0d6f7f;
            font-size:11px;
            font-weight:900;
            letter-spacing:1.6px;
            text-transform:uppercase;
        }

        .business-copy h2 {
            margin:8px 0 10px;
            font-size:30px;
            line-height:1.15;
        }

        .business-copy p {
            margin:0;
            color:#4f4f4f;
            font-size:15px;
            line-height:1.65;
            font-weight:600;
        }

        .stats-grid {
            display:grid;
            grid-template-columns:repeat(2, minmax(0, 1fr));
            gap:1px;
            overflow:hidden;
            border:1px solid #ebebeb;
            border-radius:var(--radius);
            background:#ebebeb;
        }

        .stat {
            padding:20px;
            background:#fff;
            text-align:center;
        }

        .stat strong {
            display:block;
            font-size:24px;
        }

        .stat span {
            display:block;
            margin-top:4px;
            color:#555;
            font-size:12px;
            font-weight:700;
        }

        .page-footer {
            padding:26px 0;
            background:#18212c;
            color:#c2c8d0;
            font-size:13px;
            font-weight:700;
        }

        @media (max-width:1100px) {
            .home-banner { grid-template-columns:1fr; }
            .banner-products { grid-template-columns:repeat(3, minmax(0, 1fr)); }
            .category-tiles { grid-template-columns:repeat(3, minmax(0, 1fr)); }
            .catalog-intel { grid-template-columns:1fr; }
            .workspace-grid { grid-template-columns:1fr; }
            .service-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .deal-grid { grid-template-columns:repeat(3, minmax(0, 1fr)); }
            .product-grid { grid-template-columns:repeat(3, minmax(0, 1fr)); }
            .product-carousel-track > .product-card { flex-basis:calc((100% - 28px) / 3); }
            .assistant-card, .business-card { grid-template-columns:1fr; }
        }

        @media (max-width:780px) {
            .wrap { width:min(100% - 20px, 1250px); }
            .banner-copy { padding:28px 24px; }
            .banner-copy h1 { font-size:28px; }
            .home-banner.is-full-media .banner-copy { width:100%; }
            .banner-products { grid-template-columns:1fr; }
            .hero-product { grid-template-columns:120px minmax(0, 1fr); }
            .category-tiles { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .service-grid { grid-template-columns:1fr; }
            .deal-grid, .product-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .product-carousel-track > .product-card { flex-basis:calc((100% - 14px) / 2); }
            .assistant-options { grid-template-columns:1fr; }
        }

        @media (max-width:520px) {
            .home-banner { grid-template-columns:1fr; }
            .home-banner,
            .home-banner.is-full-media,
            .home-banner.is-full-media .banner-copy { min-height:520px; }
            .banner-copy { justify-content:flex-start; }
            .banner-products { padding:0 18px 64px; }
            .hero-product { grid-template-columns:104px minmax(0, 1fr); min-height:108px; }
            .hero-product .product-media { min-height:108px; }
            .banner-controls { left:18px; right:auto; }
            .category-tiles, .deal-grid, .product-grid { grid-template-columns:1fr; }
            .product-carousel-track > .product-card { flex-basis:100%; }
            .section-head { display:block; }
            .section-title { margin-bottom:10px; }
        }

        .btn,
        .add-btn,
        .section-title {
            box-shadow:none;
        }

        .home-banner {
            border:1px solid rgba(255,255,255,.45);
        }

        .category-tile,
        .section-card,
        .product-card,
        .deal-card,
        .hero-product,
        .assistant-panel,
        .stats-grid {
            box-shadow:none;
        }

        .category-tile:hover,
        .deal-card:hover,
        .product-card:hover {
            border-color:#aebfd2;
            box-shadow:0 14px 28px rgba(24,33,44,.10);
        }

        .tile-visual,
        .deal-img,
        .product-media,
        .hero-product-img {
            background:
                linear-gradient(135deg, #ffffff 0%, #f3f6f8 55%, #e9eef5 100%);
        }

        .brands-marquee {
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#ffffff;
        }

        .brands-marquee:before { background:linear-gradient(90deg, #fff, transparent); }
        .brands-marquee:after { background:linear-gradient(270deg, #fff, transparent); }

        .assistant-card {
            border:1px solid rgba(255,255,255,.08);
            background:
                linear-gradient(135deg, #18212c 0%, #223242 52%, #102532 100%);
        }

        .business-card {
            padding:36px 32px;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#ffffff;
        }

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
    $loginUrl = $loginUrl ?? ($localePrefix . '/login');
    $registerUrl = $registerUrl ?? ($localePrefix . '/register');
    $logoutUrl = $logoutUrl ?? ($localePrefix . '/logout');
    $carouselBanners = $banners ?? [];
    $drawerCategories = array_slice($categories, 0, 12);
    $tileCategories = array_slice($categories, 0, 12);
    $brands = $topBrands ?? [];
    $quickSearches = array_slice($brands, 0, 8);
    $categoryIcon = function (string $name): string {
        $key = mb_strtolower($name);

        if (str_contains($key, 'desktop') || str_contains($key, 'notebook')) return 'laptop';
        if (str_contains($key, 'component')) return 'chip';
        if (str_contains($key, 'cable') || str_contains($key, 'adapter')) return 'cable';
        if (str_contains($key, 'network')) return 'network';
        if (str_contains($key, 'monitor') || str_contains($key, 'display')) return 'monitor';
        if (str_contains($key, 'office') || str_contains($key, 'home')) return 'printer';
        if (str_contains($key, 'apple')) return 'apple';
        if (str_contains($key, 'security')) return 'shield';
        if (str_contains($key, 'consumable')) return 'cartridge';
        if (str_contains($key, 'peripheral')) return 'mouse';
        if (str_contains($key, 'outlet')) return 'tag';

        return 'box';
    };
@endphp

@include('storefront.partials.site-header')

<main>
    <div class="wrap">
        @if($carouselBanners)
        <section class="banner-carousel" aria-label="Homepage banners" data-banner-carousel>
            @foreach($carouselBanners as $banner)
                @php
                    $heroProducts = array_slice($banner['products'] ?? [], 0, 3);
                    $bannerHasProducts = $heroProducts !== [];
                    $hasMedia = (bool) ($banner['media'] ?? '');
                    $hasCopy = (bool) (($banner['title'] ?? '') || ($banner['subtitle'] ?? '') || ($banner['button_label'] ?? ''));
                @endphp
                <article class="home-banner {{ $loop->first ? 'is-active' : '' }} {{ $hasMedia && ! $bannerHasProducts ? 'is-full-media' : '' }}" data-banner-slide>
                    @if($hasMedia)
                        <div class="banner-bg">
                            @if(($banner['media_type'] ?? 'image') === 'video')
                                <video src="{{ $banner['media'] }}" autoplay muted loop playsinline></video>
                            @else
                                <img src="{{ $banner['media'] }}" alt="{{ $banner['title'] ?: '' }}">
                            @endif
                        </div>
                    @endif

                    @if($hasCopy)
                        <div class="banner-copy">
                            @if($banner['title'] ?? '')
                                <h1>{{ $banner['title'] }}</h1>
                            @endif
                            @if($banner['subtitle'] ?? '')
                                <p>{{ $banner['subtitle'] }}</p>
                            @endif
                            @if($banner['button_label'] ?? '')
                                <a class="btn" href="{{ $banner['link_url'] ?: '#offers' }}">{{ $banner['button_label'] }}</a>
                            @endif
                        </div>
                    @endif

                    @if($bannerHasProducts)
                        <div class="banner-products">
                            @foreach($heroProducts as $product)
                                <a class="hero-product" href="{{ $product['url'] ?? '#' }}">
                                    <span class="product-media">
                                        @if($product['image'] ?? '')
                                            <span>
                                                <img src="{{ $product['image'] }}" alt="{{ $product['title'] }}" loading="lazy">
                                            </span>
                                        @else
                                            <span></span>
                                        @endif
                                    </span>
                                    <span class="hero-product-body">
                                        <strong>{{ $product['title'] }}</strong>
                                        <span class="hero-price">{{ $product['price'] }}</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </article>
            @endforeach

            @if(count($carouselBanners) > 1)
                <div class="banner-controls" aria-label="Banner controls">
                    <button class="gallery-nav" type="button" data-banner-prev aria-label="Previous banner">‹</button>
                    <div class="banner-dots">
                        @foreach($carouselBanners as $banner)
                            <button class="banner-dot {{ $loop->first ? 'is-active' : '' }}" type="button" data-banner-dot="{{ $loop->index }}" aria-label="Go to banner {{ $loop->iteration }}"></button>
                        @endforeach
                    </div>
                    <button class="gallery-nav" type="button" data-banner-next aria-label="Next banner">›</button>
                </div>
            @endif
        </section>
        @endif
        
        <section class="category-tiles" aria-label="Product categories">
            @foreach($tileCategories as $category)
                @php($icon = $categoryIcon((string) ($category['name'] ?? '')))
                <a class="category-tile" href="{{ $categoryUrl($category) }}">
                    <span class="tile-visual">
                        <span class="cat-icon" aria-hidden="true">
                            @switch($icon)
                                @case('laptop')
                                    <svg viewBox="0 0 64 64"><rect x="14" y="14" width="36" height="26" rx="3"/><path d="M8 48h48l-5-8H13l-5 8Z"/><path d="M25 44h14"/></svg>
                                    @break
                                @case('chip')
                                    <svg viewBox="0 0 64 64"><rect x="19" y="19" width="26" height="26" rx="4"/><rect x="25" y="25" width="14" height="14" rx="2"/><path d="M14 24h5M14 32h5M14 40h5M45 24h5M45 32h5M45 40h5M24 14v5M32 14v5M40 14v5M24 45v5M32 45v5M40 45v5"/></svg>
                                    @break
                                @case('cable')
                                    <svg viewBox="0 0 64 64"><path d="M18 20v10a14 14 0 0 0 28 0V20"/><path d="M14 20h8M42 20h8M18 20v-6M46 20v-6"/><path d="M32 44v8"/><path d="M24 52h16"/></svg>
                                    @break
                                @case('network')
                                    <svg viewBox="0 0 64 64"><rect x="25" y="8" width="14" height="14" rx="3"/><rect x="8" y="42" width="14" height="14" rx="3"/><rect x="42" y="42" width="14" height="14" rx="3"/><path d="M32 22v10M15 42l17-10 17 10"/></svg>
                                    @break
                                @case('monitor')
                                    <svg viewBox="0 0 64 64"><rect x="10" y="12" width="44" height="30" rx="4"/><path d="M26 42v8M38 42v8M22 52h20"/></svg>
                                    @break
                                @case('printer')
                                    <svg viewBox="0 0 64 64"><path d="M20 20V10h24v10"/><rect x="14" y="32" width="36" height="20" rx="3"/><path d="M16 32v-8h32v8M23 42h18M23 48h12"/></svg>
                                    @break
                                @case('apple')
                                    <svg viewBox="0 0 64 64"><path d="M39 10c-4 1-7 4-8 8 5 0 8-2 10-7"/><path d="M47 37c-1 8-7 17-13 17-3 0-4-2-8-2s-5 2-8 2C12 54 7 43 8 34c1-8 6-14 13-14 4 0 6 2 9 2s5-2 9-2c4 0 8 2 10 5-7 4-7 13-2 16Z"/></svg>
                                    @break
                                @case('shield')
                                    <svg viewBox="0 0 64 64"><path d="M32 8 50 16v14c0 12-7 21-18 26C21 51 14 42 14 30V16l18-8Z"/><path d="m23 32 6 6 13-14"/></svg>
                                    @break
                                @case('cartridge')
                                    <svg viewBox="0 0 64 64"><rect x="16" y="14" width="32" height="38" rx="4"/><path d="M22 14v10h20V14M24 34h16M24 42h10"/><circle cx="42" cy="44" r="2"/></svg>
                                    @break
                                @case('mouse')
                                    <svg viewBox="0 0 64 64"><rect x="20" y="8" width="24" height="48" rx="12"/><path d="M32 8v16M20 28h24"/><path d="M32 14v6"/></svg>
                                    @break
                                @case('tag')
                                    <svg viewBox="0 0 64 64"><path d="M12 34 34 12h16v16L28 50 12 34Z"/><circle cx="44" cy="20" r="3"/><path d="m24 36 12-12"/></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 64 64"><path d="m32 8 22 12-22 12L10 20 32 8Z"/><path d="M10 20v24l22 12 22-12V20"/><path d="M32 32v24"/></svg>
                            @endswitch
                        </span>
                    </span>
                    <span class="tile-bar">
                        <strong>{{ $category['name'] }}</strong>
                        <span>→</span>
                    </span>
                </a>
            @endforeach
        </section>

        <section class="section-card" aria-label="Service paths">
            <!-- <div class="section-head">
                <span class="section-title">{{ $copy['services_title'] ?? 'More than product tiles' }}</span>
                <p>{{ $copy['services_hint'] ?? 'Helpful service entry points connected to the catalog experience.' }}</p>
            </div> -->

            <div class="service-grid">
                <a class="service-card" href="{{ $searchUrl }}">
                    <span>
                        <b>PR</b>
                        <strong>{{ $copy['service_request'] ?? 'Parts request' }}</strong>
                        <span>{{ $copy['service_request_hint'] ?? 'Ask for a product that is not listed.' }}</span>
                    </span>
                </a>
                <a class="service-card" href="{{ $searchUrl }}">
                    <span>
                        <b>RD</b>
                        <strong>{{ $copy['service_repair'] ?? 'Repair desk' }}</strong>
                        <span>{{ $copy['service_repair_hint'] ?? 'Route devices, parts and warranty requests.' }}</span>
                    </span>
                </a>
                <a class="service-card" href="{{ $searchUrl }}">
                    <span>
                        <b>TI</b>
                        <strong>{{ $copy['service_trade'] ?? 'Trade-in path' }}</strong>
                        <span>{{ $copy['service_trade_hint'] ?? 'Prepare future upgrade journeys.' }}</span>
                    </span>
                </a>
                <a class="service-card" href="{{ $searchUrl }}">
                    <span>
                        <b>B2B</b>
                        <strong>{{ $copy['service_quote'] ?? 'Business quote' }}</strong>
                        <span>{{ $copy['service_quote_hint'] ?? 'Send a SKU list and prepare a company order flow.' }}</span>
                    </span>
                </a>
            </div>
        </section>

        @if($brands)
            <section class="brands-marquee" aria-label="Brands">
                <div class="brands-track">
                    @foreach(array_merge($brands, $brands) as $brand)
                        <a class="brand-name" href="{{ $searchUrl }}?q={{ urlencode($brand['name']) }}">{{ $brand['name'] }}</a>
                    @endforeach
                </div>
            </section>
        @endif
        <section class="assistant-card" aria-label="AI assistant">
            <div class="assistant-copy">
                <small>Guided product finder</small>
                <h2>Turn a large catalog into confident buying paths.</h2>
                <p>Keep the same category logic, but present it as a guided workspace for monitors, laptops, networking and upgrades. Later this can connect to stock, specs and assistant-style recommendations.</p>
            </div>
            <div class="assistant-panel">
                <label>{{ $copy['start_from_job'] ?? 'Start from the job to be done' }}</label>
                <div class="assistant-options">
                    @foreach(array_slice($tileCategories, 0, 4) as $category)
                        <a href="{{ $categoryUrl($category) }}">
                            <strong>{{ $category['name'] }}</strong>
                            <span>{{ count($category['children'] ?? []) }} {{ $copy['sections_available'] ?? 'sections available' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        @if($offers)
            <section class="section-card" id="offers">
                <div class="section-head">
                    <span class="section-title">{{ $copy['hot_deals'] ?? 'Hot Deals' }}</span>
                </div>

                <div class="deal-grid">
                    @foreach(array_slice($offers, 0, 5) as $offer)
                        <a class="deal-card" href="{{ $offer['link_url'] ?? '#' }}">
                            <span class="deal-badge">{{ $offer['discount_percent'] ?: ($copy['deal'] ?? 'Deal') }}</span>
                            <span class="deal-img">
                                @if($offer['image'] ?? '')
                                    <img src="{{ $offer['image'] }}" alt="{{ $offer['title'] }}">
                                @else
                                    <span></span>
                                @endif
                            </span>
                                <span class="deal-bar">
                                    <span class="sku-row">
                                    <span class="sku">{{ ($offer['display_code'] ?? '') ?: ($copy['hot_deals'] ?? 'Hot Deals') }}</span>
                                    <span class="stock-dot">{{ $copy['available'] ?? 'Available' }}</span>
                                </span>
                                <strong>{{ $offer['title'] }}</strong>
                                <span class="deal-price">
                                    <b>{{ $offer['offer_price'] }}</b>
                                    <span>{{ $offer['regular_price'] }}</span>
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($imageProducts)
            <section class="section-card">
                <div class="section-head">
                    <span class="section-title">{{ $copy['product_gallery'] ?? 'Product gallery' }}</span>
                </div>

                @php($galleryProducts = array_slice($imageProducts, 0, 12))

                <div class="product-carousel" data-product-carousel>
                    <div class="product-carousel-viewport">
                        <div class="product-carousel-track" data-product-track>
                            @foreach($galleryProducts as $product)
                                @include('storefront.partials.product-card', ['product' => $product])
                            @endforeach
                        </div>
                    </div>

                    @if(count($galleryProducts) > 1)
                        <div class="gallery-controls" aria-label="Product gallery controls">
                            <button class="gallery-nav" type="button" data-product-prev aria-label="Previous products">‹</button>
                            <div class="gallery-dots" data-product-dots></div>
                            <button class="gallery-nav" type="button" data-product-next aria-label="Next products">›</button>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if(($featuredSection['is_active'] ?? true) && $featuredProducts)
            <section class="section-card" id="featured">
                <div class="section-head">
                    <span class="section-title">{{ $featuredSection['title'] ?? 'Featured products' }}</span>
                </div>

                @php($featuredCarouselProducts = array_slice($featuredProducts, 0, 12))

                <div class="product-carousel" data-product-carousel>
                    <div class="product-carousel-viewport">
                        <div class="product-carousel-track" data-product-track>
                            @foreach($featuredCarouselProducts as $product)
                                @include('storefront.partials.product-card', ['product' => $product])
                            @endforeach
                        </div>
                    </div>

                    @if(count($featuredCarouselProducts) > 1)
                        <div class="gallery-controls" aria-label="Featured products controls">
                            <button class="gallery-nav" type="button" data-product-prev aria-label="Previous products">‹</button>
                            <div class="gallery-dots" data-product-dots></div>
                            <button class="gallery-nav" type="button" data-product-next aria-label="Next products">›</button>
                        </div>
                    @endif
                </div>
            </section>
        @endif
        <!-- The business card section is currently commented out, but can be enabled when needed. It provides an overview of the catalog's scale and capabilities. -->
        <!-- <section class="business-card">
            <div class="business-copy">
                <small>Operating layer</small>
                <h2>{{ $copy['business_title'] ?? 'Built for catalog scale, buyer speed and cleaner decisions.' }}</h2>
                <p>{{ $copy['business_copy'] ?? 'Enterprise monitors, laptops, networking, components and infrastructure, presented as a modern buying surface powered by live database records.' }}</p>
            </div>
            <div class="stats-grid">
                <div class="stat"><strong>{{ number_format($catalogStats['products'] ?? 0) }}</strong><span>{{ $copy['catalog_products'] ?? 'Catalog products' }}</span></div>
                <div class="stat"><strong>{{ number_format($catalogStats['brands'] ?? 0) }}</strong><span>{{ $copy['brands_indexed'] ?? 'Brands indexed' }}</span></div>
                <div class="stat"><strong>{{ number_format($catalogStats['images'] ?? 0) }}</strong><span>{{ $copy['imported_images'] ?? 'Imported images' }}</span></div>
                <div class="stat"><strong>Live</strong><span>{{ $copy['database_powered'] ?? 'Database powered' }}</span></div>
            </div>
        </section> -->

        @if($latestProducts)
            <section class="section-card" id="new">
                <div class="section-head">
                    <span class="section-title">{{ $copy['new_in'] ?? 'New In' }}</span>
                </div>

                @php($latestCarouselProducts = array_slice($latestProducts, 0, 12))

                <div class="product-carousel" data-product-carousel>
                    <div class="product-carousel-viewport">
                        <div class="product-carousel-track" data-product-track>
                            @foreach($latestCarouselProducts as $product)
                                @include('storefront.partials.product-card', ['product' => $product])
                            @endforeach
                        </div>
                    </div>

                    @if(count($latestCarouselProducts) > 1)
                        <div class="gallery-controls" aria-label="New in products controls">
                            <button class="gallery-nav" type="button" data-product-prev aria-label="Previous products">‹</button>
                            <div class="gallery-dots" data-product-dots></div>
                            <button class="gallery-nav" type="button" data-product-next aria-label="Next products">›</button>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </div>
</main>

@include('storefront.partials.site-footer')

@include('storefront.partials.site-header-script')

<script>
    (() => {

        document.querySelectorAll('[data-banner-carousel]').forEach((carousel) => {
            const slides = Array.from(carousel.querySelectorAll('[data-banner-slide]'));
            const dots = Array.from(carousel.querySelectorAll('[data-banner-dot]'));
            const previous = carousel.querySelector('[data-banner-prev]');
            const next = carousel.querySelector('[data-banner-next]');

            if (slides.length <= 1) {
                return;
            }

            let activeIndex = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
            let autoplay = null;

            const showSlide = (index) => {
                activeIndex = (index + slides.length) % slides.length;

                slides.forEach((slide, slideIndex) => {
                    slide.classList.toggle('is-active', slideIndex === activeIndex);
                });

                dots.forEach((dot, dotIndex) => {
                    dot.classList.toggle('is-active', dotIndex === activeIndex);
                });
            };

            const stopAutoplay = () => {
                if (autoplay) {
                    window.clearInterval(autoplay);
                    autoplay = null;
                }
            };

            const startAutoplay = () => {
                stopAutoplay();
                autoplay = window.setInterval(() => showSlide(activeIndex + 1), 5000);
            };

            previous?.addEventListener('click', () => {
                showSlide(activeIndex - 1);
                startAutoplay();
            });

            next?.addEventListener('click', () => {
                showSlide(activeIndex + 1);
                startAutoplay();
            });

            dots.forEach((dot) => {
                dot.addEventListener('click', () => {
                    showSlide(Number(dot.dataset.bannerDot || 0));
                    startAutoplay();
                });
            });

            carousel.addEventListener('mouseenter', stopAutoplay);
            carousel.addEventListener('mouseleave', startAutoplay);
            carousel.addEventListener('focusin', stopAutoplay);
            carousel.addEventListener('focusout', startAutoplay);

            startAutoplay();
        });

        document.querySelectorAll('[data-product-carousel]').forEach((carousel) => {
            const track = carousel.querySelector('[data-product-track]');
            const items = Array.from(track?.children || []);
            const previous = carousel.querySelector('[data-product-prev]');
            const next = carousel.querySelector('[data-product-next]');
            const dotsContainer = carousel.querySelector('[data-product-dots]');

            if (!track || items.length <= 1) {
                return;
            }

            let activePage = 0;
            let pages = 1;
            let autoplay = null;

            const visibleCount = () => {
                const viewportWidth = carousel.querySelector('.product-carousel-viewport')?.clientWidth || carousel.clientWidth;
                const firstItemWidth = items[0]?.getBoundingClientRect().width || viewportWidth;
                const gap = Number.parseFloat(window.getComputedStyle(track).gap || '0');

                return Math.max(1, Math.floor((viewportWidth + gap) / (firstItemWidth + gap)));
            };

            const buildDots = () => {
                if (!dotsContainer) {
                    return;
                }

                dotsContainer.innerHTML = '';

                for (let index = 0; index < pages; index += 1) {
                    const dot = document.createElement('button');
                    dot.className = `gallery-dot${index === activePage ? ' is-active' : ''}`;
                    dot.type = 'button';
                    dot.setAttribute('aria-label', `Go to product group ${index + 1}`);
                    dot.addEventListener('click', () => {
                        showPage(index);
                        startAutoplay();
                    });
                    dotsContainer.appendChild(dot);
                }
            };

            const showPage = (page) => {
                const perPage = visibleCount();
                pages = Math.max(1, Math.ceil(items.length / perPage));
                activePage = (page + pages) % pages;

                const targetIndex = Math.min(activePage * perPage, Math.max(0, items.length - perPage));
                const targetItem = items[targetIndex];
                const offset = targetItem ? targetItem.offsetLeft : 0;

                track.style.transform = `translateX(${-offset}px)`;

                dotsContainer?.querySelectorAll('.gallery-dot').forEach((dot, index) => {
                    dot.classList.toggle('is-active', index === activePage);
                });
            };

            const refresh = () => {
                const perPage = visibleCount();
                pages = Math.max(1, Math.ceil(items.length / perPage));
                activePage = Math.min(activePage, pages - 1);
                buildDots();
                showPage(activePage);

                const controls = carousel.querySelector('.gallery-controls');

                if (controls) {
                    controls.hidden = pages <= 1;
                }
            };

            const stopAutoplay = () => {
                if (autoplay) {
                    window.clearInterval(autoplay);
                    autoplay = null;
                }
            };

            const startAutoplay = () => {
                stopAutoplay();

                if (pages > 1) {
                    autoplay = window.setInterval(() => showPage(activePage + 1), 5000);
                }
            };

            previous?.addEventListener('click', () => {
                showPage(activePage - 1);
                startAutoplay();
            });

            next?.addEventListener('click', () => {
                showPage(activePage + 1);
                startAutoplay();
            });

            window.addEventListener('resize', () => {
                refresh();
                startAutoplay();
            });

            carousel.addEventListener('mouseenter', stopAutoplay);
            carousel.addEventListener('mouseleave', startAutoplay);
            carousel.addEventListener('focusin', stopAutoplay);
            carousel.addEventListener('focusout', startAutoplay);

            refresh();
            startAutoplay();
        });
    })();
</script>
</body>
</html>
