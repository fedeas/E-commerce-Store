<!doctype html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($copy['b2b_request'] ?? 'Request B2B') . ' | ' . config('app.name') }}</title>
    @include('storefront.partials.site-fonts')
    <style>
        :root { --dark:#18212c; --green:#00a6a6; --green-2:#0d7b83; --line:#dce5ee; --muted:#667789; --radius:8px; --bg:#edf3f7; --red:#e3342f; --shadow:0 18px 45px rgba(24,33,44,.12); }
        @include('storefront.partials.site-font-styles')
        * { box-sizing:border-box; }
        body { margin:0; font-family:var(--font-sans); background:var(--bg); color:#18212c; }
        a { color:inherit; text-decoration:none; }
        button, input, textarea { font:inherit; }
        .wrap { width:min(100% - 28px, 1250px); margin:0 auto; }
        .request-shell { padding:34px 0 56px; }
        .request-grid { display:grid; grid-template-columns:minmax(0, 1fr) 520px; gap:18px; align-items:start; }
        .request-hero, .request-card { border:1px solid var(--line); border-radius:var(--radius); background:#fff; box-shadow:var(--shadow); overflow:hidden; }
        .request-hero { min-height:520px; padding:42px; display:flex; flex-direction:column; justify-content:space-between; background:linear-gradient(135deg,#162231 0%,#203244 52%,#0d7b83 100%); color:#fff; }
        .request-hero small { color:#7ee7e0; font-size:12px; font-weight:950; letter-spacing:1.6px; text-transform:uppercase; }
        .request-hero h1 { max-width:640px; margin:12px 0 0; font-size:46px; line-height:1.02; letter-spacing:0; }
        .request-hero p { max-width:600px; margin:16px 0 0; color:#d7e6ea; font-size:15px; line-height:1.65; font-weight:650; }
        .request-points { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
        .request-point { min-height:94px; padding:14px; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); background:rgba(255,255,255,.08); }
        .request-point strong { display:block; font-size:13px; }
        .request-point span { display:block; margin-top:8px; color:#d7e6ea; font-size:12px; line-height:1.45; font-weight:650; }
        .request-card { padding:28px; }
        .request-card h2 { margin:0; font-size:27px; line-height:1.1; }
        .request-card > p { margin:8px 0 22px; color:var(--muted); font-size:13px; font-weight:750; line-height:1.5; }
        .status { margin-bottom:14px; padding:11px 12px; border:1px solid #bfe8d3; border-radius:var(--radius); background:#effbf5; color:#17643b; font-size:12px; font-weight:850; }
        .field-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
        .field { margin-top:14px; }
        .field.full { grid-column:1 / -1; }
        .field label { display:block; margin-bottom:7px; font-size:11px; font-weight:950; text-transform:uppercase; letter-spacing:.8px; color:#344154; }
        .field input, .field textarea { width:100%; border:1px solid #cfdbe7; border-radius:var(--radius); padding:0 13px; background:#fff; color:#18212c; outline:none; }
        .field input { height:45px; }
        .field textarea { min-height:96px; padding-top:12px; resize:vertical; }
        .field input:focus, .field textarea:focus { border-color:#0d7b83; box-shadow:0 0 0 3px rgba(13,123,131,.12); }
        .error { margin-top:6px; color:var(--red); font-size:12px; font-weight:750; }
        .request-row { margin-top:22px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .request-row span { color:var(--muted); font-size:12px; font-weight:800; }
        .request-btn { height:42px; min-width:132px; border:0; border-radius:var(--radius); background:#00a6a6; color:#fff; font-size:12px; font-weight:950; cursor:pointer; }
        @media (max-width:960px) { .request-grid { grid-template-columns:1fr; } .request-hero { min-height:360px; } .request-hero h1 { font-size:36px; } }
        @media (max-width:560px) { .request-shell { padding-top:18px; } .request-hero { padding:24px; } .request-points, .field-grid { grid-template-columns:1fr; } .request-card { padding:20px; } .field.full { grid-column:auto; } .request-row { align-items:stretch; flex-direction:column; } .request-btn { width:100%; } }
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
    $b2bRequestUrl = $localePrefix . '/b2b-request';
@endphp
@include('storefront.partials.site-header')
<main class="request-shell">
    <div class="wrap request-grid">
        <section class="request-hero">
            <div>
                <small>{{ $copy['business_catalog'] ?? 'Business catalog' }}</small>
                <h1>{{ $copy['b2b_request_title'] ?? 'Request wholesale pricing' }}</h1>
                <p>{{ $copy['b2b_request_hint'] ?? 'Send your company details and an admin will switch the account to wholesale pricing.' }}</p>
            </div>
            <div class="request-points">
                <div class="request-point"><strong>{{ $copy['b2b_status'] ?? 'Pricing status' }}</strong><span>{{ auth()->user()->hasPendingB2bRequest() ? ($copy['b2b_pending'] ?? 'B2B pending') : ($copy['tax_note_b2b'] ?? 'B2B wholesale price, without VAT.') }}</span></div>
                <div class="request-point"><strong>{{ $copy['business_quote'] ?? 'Quotes' }}</strong><span>{{ $copy['service_quote_hint'] ?? 'Prepare a company order flow.' }}</span></div>
                <div class="request-point"><strong>{{ $copy['live_catalog'] ?? 'Live catalog' }}</strong><span>{{ $copy['search_results_hint'] ?? 'Search by SKU, MPN and EAN.' }}</span></div>
            </div>
        </section>

        <section class="request-card">
            <h2>{{ $copy['b2b_request'] ?? 'Request B2B' }}</h2>
            <p>{{ $copy['b2b_request_hint'] ?? 'Send company details for review.' }}</p>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ $b2bRequestUrl }}">
                @csrf
                <div class="field-grid">
                    <div class="field full">
                        <label for="company_name">{{ $copy['company_name'] ?? 'Company name' }}</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}" required>
                        @error('company_name') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="vat_number">{{ $copy['vat_number'] ?? 'VAT number' }}</label>
                        <input id="vat_number" type="text" name="vat_number" value="{{ old('vat_number', $user->vat_number) }}">
                        @error('vat_number') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="occupation">{{ $copy['occupation'] ?? 'Occupation' }}</label>
                        <input id="occupation" type="text" name="occupation" value="{{ old('occupation', $user->occupation) }}">
                        @error('occupation') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field full">
                        <label for="company_address">{{ $copy['company_address'] ?? 'Company address' }}</label>
                        <input id="company_address" type="text" name="company_address" value="{{ old('company_address', $user->company_address) }}">
                        @error('company_address') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="city">{{ $copy['city'] ?? 'City' }}</label>
                        <input id="city" type="text" name="city" value="{{ old('city', $user->city) }}">
                        @error('city') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="postcode">{{ $copy['postcode'] ?? 'Postcode' }}</label>
                        <input id="postcode" type="text" name="postcode" value="{{ old('postcode', $user->postcode) }}">
                        @error('postcode') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="phone">{{ $copy['phone'] ?? 'Phone' }}</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field">
                        <label for="mobile">{{ $copy['mobile'] ?? 'Mobile' }}</label>
                        <input id="mobile" type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}">
                        @error('mobile') <div class="error">{{ $message }}</div> @enderror
                    </div>

                    <div class="field full">
                        <label for="customer_notes">{{ $copy['notes'] ?? 'Notes' }}</label>
                        <textarea id="customer_notes" name="customer_notes">{{ old('customer_notes', $user->customer_notes) }}</textarea>
                        @error('customer_notes') <div class="error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="request-row">
                    <span>{{ $copy['b2b_status'] ?? 'Pricing status' }}: {{ $user->isB2b() ? ($copy['b2b_active'] ?? 'B2B active') : ($user->hasPendingB2bRequest() ? ($copy['b2b_pending'] ?? 'B2B pending') : ($copy['retail'] ?? 'Retail')) }}</span>
                    <button class="request-btn" type="submit">{{ $copy['send_request'] ?? 'Send request' }}</button>
                </div>
            </form>
        </section>
    </div>
</main>
@include('storefront.partials.site-footer')
@include('storefront.partials.site-header-script')
</body>
</html>
