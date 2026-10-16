<!doctype html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($copy['sign_in'] ?? 'Sign in') . ' | ' . config('app.name') }}</title>
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
        .auth-grid { display:grid; grid-template-columns:minmax(0, 1fr) 420px; gap:18px; align-items:stretch; }
        .auth-hero, .auth-card { border:1px solid var(--line); border-radius:var(--radius); background:#fff; box-shadow:var(--shadow); overflow:hidden; }
        .auth-hero { min-height:520px; padding:42px; display:flex; flex-direction:column; justify-content:space-between; background:linear-gradient(135deg,#162231 0%,#203244 52%,#0d7b83 100%); color:#fff; }
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
        .status { margin-bottom:14px; padding:11px 12px; border:1px solid #bfe8d3; border-radius:var(--radius); background:#effbf5; color:#17643b; font-size:12px; font-weight:850; }
        .field { margin-top:14px; }
        .field label { display:block; margin-bottom:7px; font-size:11px; font-weight:950; text-transform:uppercase; letter-spacing:.8px; color:#344154; }
        .field input { width:100%; height:45px; border:1px solid #cfdbe7; border-radius:var(--radius); padding:0 13px; background:#fff; color:#18212c; outline:none; }
        .field input:focus { border-color:#0d7b83; box-shadow:0 0 0 3px rgba(13,123,131,.12); }
        .error { margin-top:6px; color:var(--red); font-size:12px; font-weight:750; }
        .remember { margin-top:14px; display:flex; align-items:center; gap:8px; color:#526374; font-size:12px; font-weight:800; }
        .auth-row { margin-top:22px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
        .auth-link { color:#0d7b83; font-size:12px; font-weight:900; }
        .auth-btn { height:42px; min-width:118px; border:0; border-radius:var(--radius); background:#00a6a6; color:#fff; font-size:12px; font-weight:950; cursor:pointer; }
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
                <h1>{{ $copy['login_title'] ?? 'Access your account' }}</h1>
                <p>{{ $copy['login_hint'] ?? 'Sign in to keep cart sessions, request quotes, and prepare account pricing workflows for your company.' }}</p>
            </div>
            <div class="auth-points">
                <div class="auth-point"><strong>{{ $copy['cart'] ?? 'Cart' }}</strong><span>{{ $copy['cart_empty_hint'] ?? 'Save products for quotes and orders.' }}</span></div>
                <div class="auth-point"><strong>{{ $copy['business_quote'] ?? 'Quotes' }}</strong><span>{{ $copy['service_quote_hint'] ?? 'Prepare a company order flow.' }}</span></div>
                <div class="auth-point"><strong>{{ $copy['live_catalog'] ?? 'Live catalog' }}</strong><span>{{ $copy['search_results_hint'] ?? 'Search by SKU, MPN and EAN.' }}</span></div>
            </div>
        </section>

        <section class="auth-card">
            <h2>{{ $copy['sign_in'] ?? 'Sign in' }}</h2>
            <p>{{ $copy['sign_in_hint'] ?? 'Sign in to get account pricing and saved buying lists' }}</p>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ $loginUrl }}">
                @csrf
                <div class="field">
                    <label for="email">{{ $copy['email'] ?? 'Email' }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                    @error('email') <div class="error">{{ $message }}</div> @enderror
                </div>

                <div class="field">
                    <label for="password">{{ $copy['password'] ?? 'Password' }}</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password">
                    @error('password') <div class="error">{{ $message }}</div> @enderror
                </div>

                <label class="remember" for="remember_me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span>{{ $copy['remember_me'] ?? 'Remember me' }}</span>
                </label>

                <div class="auth-row">
                    <a class="auth-link" href="{{ route('password.request') }}">{{ $copy['forgot_password'] ?? 'Forgot password?' }}</a>
                    <button class="auth-btn" type="submit">{{ $copy['sign_in'] ?? 'Sign in' }}</button>
                </div>
            </form>

            <div class="switch">
                {{ $copy['no_account'] ?? 'No account yet?' }}
                <a href="{{ $registerUrl }}">{{ $copy['register'] ?? 'Register' }}</a>
            </div>
        </section>
    </div>
</main>
@include('storefront.partials.site-header-script')
</body>
</html>
