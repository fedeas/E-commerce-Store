<!doctype html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($copy['register'] ?? 'Register') . ' | ' . config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --dark:#18212c; --green-2:#0d7b83; --line:#dce5ee; --muted:#667789; --radius:8px; --bg:#edf3f7; --red:#e3342f; --shadow:0 18px 45px rgba(24,33,44,.12); }
        * { box-sizing:border-box; }
        body { margin:0; font-family:'Plus Jakarta Sans', Arial, sans-serif; background:var(--bg); color:#18212c; }
        a { color:inherit; text-decoration:none; }
        button, input { font:inherit; }
        .wrap { width:min(100% - 28px, 1250px); margin:0 auto; }
        .auth-shell { padding:34px 0 56px; }
        .auth-grid { display:grid; grid-template-columns:minmax(0, 1fr) 440px; gap:18px; align-items:stretch; }
        .auth-hero, .auth-card { border:1px solid var(--line); border-radius:var(--radius); background:#fff; box-shadow:var(--shadow); overflow:hidden; }
        .auth-hero { min-height:560px; padding:42px; display:flex; flex-direction:column; justify-content:space-between; background:linear-gradient(135deg,#162231 0%,#203244 52%,#0d7b83 100%); color:#fff; }
        .auth-hero small { color:#7ee7e0; font-size:12px; font-weight:950; letter-spacing:1.6px; text-transform:uppercase; }
        .auth-hero h1 { max-width:620px; margin:12px 0 0; font-size:46px; line-height:1.02; letter-spacing:0; }
        .auth-hero p { max-width:560px; margin:16px 0 0; color:#d7e6ea; font-size:15px; line-height:1.65; font-weight:650; }
        .auth-points { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
        .auth-point { min-height:94px; padding:14px; border:1px solid rgba(255,255,255,.16); border-radius:var(--radius); background:rgba(255,255,255,.08); }
        .auth-point strong { display:block; font-size:13px; }
        .auth-point span { display:block; margin-top:8px; color:#d7e6ea; font-size:12px; line-height:1.45; font-weight:650; }
        .auth-card { padding:28px; }
        .auth-card h2 { margin:0; font-size:27px; line-height:1.1; }
        .auth-card > p { margin:8px 0 22px; color:var(--muted); font-size:13px; font-weight:750; line-height:1.5; }
        .field { margin-top:14px; }
        .field label { display:block; margin-bottom:7px; font-size:11px; font-weight:950; text-transform:uppercase; letter-spacing:.8px; color:#344154; }
        .field input { width:100%; height:45px; border:1px solid #cfdbe7; border-radius:var(--radius); padding:0 13px; background:#fff; color:#18212c; outline:none; }
        .field input:focus { border-color:#0d7b83; box-shadow:0 0 0 3px rgba(13,123,131,.12); }
        .error { margin-top:6px; color:var(--red); font-size:12px; font-weight:750; }
        .auth-row { margin-top:22px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .auth-link { color:#0d7b83; font-size:12px; font-weight:900; }
        .auth-btn { height:42px; min-width:124px; border:0; border-radius:var(--radius); background:#00a6a6; color:#fff; font-size:12px; font-weight:950; cursor:pointer; }
        .switch { margin-top:18px; padding-top:18px; border-top:1px solid var(--line); color:var(--muted); font-size:13px; font-weight:750; }
        .switch a { color:#18212c; font-weight:950; }
        @media (max-width:900px) { .auth-grid { grid-template-columns:1fr; } .auth-hero { min-height:360px; } .auth-hero h1 { font-size:36px; } }
        @media (max-width:560px) { .auth-shell { padding-top:18px; } .auth-hero { padding:24px; } .auth-points { grid-template-columns:1fr; } .auth-card { padding:20px; } .auth-row { align-items:stretch; flex-direction:column; } .auth-btn { width:100%; } }
        @include('storefront.partials.site-header-styles')
    </style>
</head>
<body>
@php
    $copy = $copy ?? [];
    $locale = $locale ?? 'en';
    $localePrefix = '/' . $locale;
    $homeUrl = $localePrefix;
    $searchUrl = $localePrefix . '/search';
    $loginUrl = $localePrefix . '/login';
    $registerUrl = $localePrefix . '/register';
@endphp
@include('storefront.partials.site-header')
<main class="auth-shell">
    <div class="wrap auth-grid">
        <section class="auth-hero">
            <div>
                <small>{{ $copy['business_catalog'] ?? 'Business catalog' }}</small>
                <h1>{{ $copy['register_title'] ?? 'Create your account' }}</h1>
                <p>{{ $copy['register_hint'] ?? 'Register once, then build carts and buying lists around the live catalog.' }}</p>
            </div>
            <div class="auth-points">
                <div class="auth-point"><strong>{{ $copy['live_catalog'] ?? 'Live catalog' }}</strong><span>{{ $copy['product_gallery_hint'] ?? 'Live catalog products with imported images.' }}</span></div>
                <div class="auth-point"><strong>{{ $copy['business_quote'] ?? 'Quotes' }}</strong><span>{{ $copy['service_quote_hint'] ?? 'Prepare a company order flow.' }}</span></div>
                <div class="auth-point"><strong>{{ $copy['responsive'] ?? 'Responsive' }}</strong><span>{{ $copy['responsive_hint'] ?? 'Catalog tools collapse cleanly on app widths.' }}</span></div>
            </div>
        </section>

        <section class="auth-card">
            <h2>{{ $copy['register'] ?? 'Register' }}</h2>
            <p>{{ $copy['register_hint'] ?? 'Create an account for saved buying workflows.' }}</p>

            <form method="POST" action="{{ $registerUrl }}">
                @csrf
                <div class="field">
                    <label for="name">{{ $copy['name'] ?? 'Name' }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
                    @error('name') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="email">{{ $copy['email'] ?? 'Email' }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="password">{{ $copy['password'] ?? 'Password' }}</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="password_confirmation">{{ $copy['confirm_password'] ?? 'Confirm password' }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="auth-row">
                    <a class="auth-link" href="{{ $loginUrl }}">{{ $copy['already_registered'] ?? 'Already registered?' }}</a>
                    <button class="auth-btn" type="submit">{{ $copy['register'] ?? 'Register' }}</button>
                </div>
            </form>

            <div class="switch">
                {{ $copy['already_have_account'] ?? 'Already have an account?' }}
                <a href="{{ $loginUrl }}">{{ $copy['sign_in'] ?? 'Sign in' }}</a>
            </div>
        </section>
    </div>
</main>
@include('storefront.partials.site-header-script')
</body>
</html>
