<!DOCTYPE html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['contact_title'] ?? 'Contact us' }} | {{ config('app.name') }}</title>
    @include('storefront.partials.site-fonts')
    <style>
        :root { --ink:#20242c; --muted:#667085; --line:#d9e0e8; --soft:#f3f6f8; --dark:#18212c; --green:#00a6a6; --red:#c2413a; --radius:8px; --shadow:0 18px 45px rgba(24,33,44,.12); }
        @include('storefront.partials.site-font-styles')
        * { box-sizing:border-box; }
        body { margin:0; font-family:var(--font-sans); background:#eef2f5; color:var(--ink); letter-spacing:0; }
        a { color:inherit; text-decoration:none; }
        img { display:block; max-width:100%; }
        button, input, select, textarea { font:inherit; letter-spacing:0; }
        button { cursor:pointer; }
        .wrap { width:min(1250px, calc(100% - 32px)); margin:0 auto; }
        main { padding:24px 0 56px; }
        .contact-hero {
            padding:34px;
            border-radius:var(--radius);
            background:linear-gradient(135deg,#18212c 0%,#1f3645 58%,#0c7375 100%);
            color:#fff;
            box-shadow:var(--shadow);
        }
        .contact-hero small { color:#7dd3fc; font-size:11px; font-weight:950; letter-spacing:1.5px; text-transform:uppercase; }
        .contact-hero h1 { max-width:720px; margin:10px 0 0; font-size:42px; line-height:1.05; }
        .contact-hero p { max-width:760px; margin:14px 0 0; color:#d8e5ee; font-size:15px; line-height:1.65; font-weight:750; }
        .contact-layout { margin-top:18px; display:grid; grid-template-columns:minmax(0, 1fr) 420px; gap:18px; align-items:start; }
        .store-grid { display:grid; gap:18px; }
        .store-card, .contact-form-card {
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#fff;
            overflow:hidden;
            box-shadow:0 10px 25px rgba(24,33,44,.05);
        }
        .store-map { height:260px; background:#dfe8ef; border-bottom:1px solid var(--line); }
        .store-map iframe { width:100%; height:100%; border:0; display:block; }
        .store-body { padding:18px; display:grid; gap:12px; }
        .store-body h2 { margin:0; font-size:22px; }
        .store-detail { display:grid; gap:5px; color:#34404a; font-size:13px; font-weight:750; }
        .store-detail small { color:var(--muted); font-size:10px; font-weight:950; letter-spacing:1px; text-transform:uppercase; }
        .store-actions { display:flex; flex-wrap:wrap; gap:8px; padding-top:4px; }
        .store-actions a {
            min-height:38px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0 12px;
            border:1px solid var(--line);
            border-radius:var(--radius);
            background:#f8fafc;
            color:#18212c;
            font-size:12px;
            font-weight:950;
        }
        .store-actions a:first-child { background:var(--green); border-color:var(--green); color:#fff; }
        .contact-form-card { padding:22px; position:sticky; top:90px; }
        .form-head small { color:#0d6f7f; font-size:11px; font-weight:950; letter-spacing:1.3px; text-transform:uppercase; }
        .form-head h2 { margin:8px 0 0; font-size:26px; line-height:1.12; }
        .form-head p { margin:9px 0 0; color:var(--muted); font-size:13px; line-height:1.55; font-weight:750; }
        .notice { margin:16px 0 0; padding:12px 14px; border-radius:var(--radius); border:1px solid #b7e2d7; background:#effaf6; color:#0d6f4f; font-size:13px; font-weight:850; }
        .field-grid { margin-top:18px; display:grid; gap:12px; }
        .field { display:grid; gap:7px; }
        .field label { color:#394554; font-size:11px; font-weight:950; letter-spacing:.8px; text-transform:uppercase; }
        .field input, .field select, .field textarea {
            width:100%;
            min-height:44px;
            border:1px solid #cfd8e3;
            border-radius:var(--radius);
            background:#fff;
            color:#18212c;
            padding:0 12px;
            outline:0;
            font-size:14px;
            font-weight:700;
        }
        .field textarea { min-height:146px; padding:12px; resize:vertical; line-height:1.5; }
        .field input:focus, .field select:focus, .field textarea:focus { border-color:#00a6a6; box-shadow:0 0 0 3px rgba(0,166,166,.12); }
        .error { color:#d9362e; font-size:12px; font-weight:850; }
        .submit-btn {
            min-height:46px;
            border:0;
            border-radius:var(--radius);
            background:var(--green);
            color:#fff;
            font-size:13px;
            font-weight:950;
        }
        @media (max-width:980px) {
            .contact-layout { grid-template-columns:1fr; }
            .contact-form-card { position:static; }
        }
        @media (max-width:560px) {
            .wrap { width:min(100% - 20px, 1250px); }
            .contact-hero { padding:26px 22px; }
            .contact-hero h1 { font-size:32px; }
            .store-map { height:220px; }
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
@endphp

@include('storefront.partials.site-header')

<main>
    <div class="wrap">
        <section class="contact-hero">
            <small>{{ config('app.name') }}</small>
            <h1>{{ $copy['contact_title'] ?? 'Contact us' }}</h1>
            <p>{{ $copy['contact_intro'] ?? 'Visit one of our stores, call us, or send a message directly from the storefront.' }}</p>
        </section>

        <div class="contact-layout">
            <section class="store-grid" aria-label="{{ $copy['contact'] ?? 'Contact' }}">
                @foreach($stores as $store)
                    <article class="store-card">
                        <div class="store-map">
                            <iframe
                                src="{{ $store['map'] }}"
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"
                                title="{{ $copy[$store['name_key']] ?? $store['key'] }} map"></iframe>
                        </div>
                        <div class="store-body">
                            <h2>{{ $copy[$store['name_key']] ?? $store['key'] }}</h2>
                            <div class="store-detail">
                                <small>{{ $copy['contact_address'] ?? 'Address' }}</small>
                                <span>{{ $store['address'] }}</span>
                            </div>
                            <div class="store-detail">
                                <small>{{ $copy['phone'] ?? 'Phone' }}</small>
                                <span>{{ $store['phone'] }}</span>
                            </div>
                            <div class="store-detail">
                                <small>{{ $copy['fax'] ?? 'Fax' }}</small>
                                <span>{{ $store['fax'] }}</span>
                            </div>
                            <div class="store-detail">
                                <small>{{ $copy['email'] ?? 'Email' }}</small>
                                <span>{{ $store['email'] }}</span>
                            </div>
                            <div class="store-actions">
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $store['phone']) }}">{{ $copy['phone'] ?? 'Phone' }}</a>
                                <a href="mailto:{{ $store['email'] }}">{{ $copy['email'] ?? 'Email' }}</a>
                                <a href="{{ $store['map_link'] ?? str_replace('&output=embed', '', $store['map']) }}" target="_blank" rel="noopener">Google Maps</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            <aside class="contact-form-card">
                <div class="form-head">
                    <small>{{ $copy['send_request'] ?? 'Send request' }}</small>
                    <h2>{{ $copy['contact'] ?? 'Contact' }}</h2>
                    <p>{{ $copy['contact_form_hint'] ?? 'Send us a message and the right store team will get back to you.' }}</p>
                </div>

                @if(session('status'))
                    <div class="notice">{{ session('status') }}</div>
                @endif

                <form class="field-grid" method="post" action="{{ $contactUrl ?? ($localePrefix . '/contact') }}">
                    @csrf
                    <div class="field">
                        <label for="name">{{ $copy['name'] ?? 'Name' }}</label>
                        <input id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}" required>
                        @error('name') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="email">{{ $copy['email'] ?? 'Email' }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" required>
                        @error('email') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="phone">{{ $copy['phone'] ?? 'Phone' }}</label>
                        <input id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="store">{{ $copy['contact_choose_store'] ?? 'Preferred store' }}</label>
                        <select id="store" name="store">
                            <option value=""></option>
                            @foreach($stores as $store)
                                <option value="{{ $copy[$store['name_key']] ?? $store['key'] }}" @selected(old('store') === ($copy[$store['name_key']] ?? $store['key']))>
                                    {{ $copy[$store['name_key']] ?? $store['key'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label for="message">{{ $copy['contact_message'] ?? 'Your message' }}</label>
                        <textarea id="message" name="message" required>{{ old('message') }}</textarea>
                        @error('message') <span class="error">{{ $message }}</span> @enderror
                    </div>
                    <button class="submit-btn" type="submit">{{ $copy['send_request'] ?? 'Send request' }}</button>
                </form>
            </aside>
        </div>
    </div>
</main>

@include('storefront.partials.site-footer')
@include('storefront.partials.site-header-script')
</body>
</html>
