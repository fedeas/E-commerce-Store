<!DOCTYPE html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['checkout'] ?? 'Checkout' }} | {{ config('app.name') }}</title>
    @include('storefront.partials.site-fonts')
    <style>
        :root { --ink:#20242c; --muted:#667085; --line:#d9e0e8; --soft:#f3f6f8; --dark:#18212c; --green:#00a6a6; --red:#c2413a; --blue:#243b8f; --radius:8px; }
        @include('storefront.partials.site-font-styles')
        * { box-sizing:border-box; }
        body { margin:0; font-family:var(--font-sans); background:#f4f6f8; color:var(--ink); letter-spacing:0; }
        a { color:inherit; text-decoration:none; }
        button, input, textarea { font:inherit; letter-spacing:0; }
        .wrap { width:min(1376px, calc(100% - 32px)); margin:0 auto; }
        main { padding:18px 0 48px; }
        .checkout-shell { background:#fff; border:1px solid #d8dde3; }
        .steps { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:13px; margin-bottom:10px; }
        .step { min-height:50px; display:flex; align-items:center; justify-content:center; gap:8px; background:#e8e8e8; color:#8b8f94; font-size:18px; font-weight:950; text-transform:uppercase; }
        .step.is-active { background:#050505; color:#fff; position:relative; }
        .checkout-form { padding:0 0 16px; }
        .customer-strip { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:10px; padding:14px; border:1px solid #d7dce2; border-bottom:0; background:#fbfcfd; }
        .field { display:grid; gap:5px; }
        .field label { color:#4d5661; font-size:11px; font-weight:950; text-transform:uppercase; }
        .field input, .comments textarea { width:100%; border:1px solid #cfd5dc; border-radius:0; background:#fff; color:#111827; outline:0; font-weight:750; }
        .field input { min-height:38px; padding:0 9px; }
        .field input:focus, .comments textarea:focus { border-color:#00a6a6; box-shadow:0 0 0 2px rgba(0,166,166,.11); }
        .shipping-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); border:1px solid #d7dce2; }
        .ship-card { min-height:355px; position:relative; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:28px 24px; border-right:1px solid #d7dce2; cursor:pointer; background:#fff; }
        .ship-card:last-child { border-right:0; }
        .ship-card input { position:absolute; opacity:0; pointer-events:none; }
        .ship-card:has(input:checked) { box-shadow:inset 0 0 0 4px #00a6a6; }
        .ship-card:has(input:checked) .ship-title { color:#007d86; }
        .ship-icon { width:160px; height:118px; margin-bottom:18px; color:#333; }
        .ship-title { margin:0; font-size:20px; line-height:1.12; font-weight:950; color:#050505; }
        .ship-address { margin:8px auto 0; max-width:260px; font-size:13px; line-height:1.45; color:#111827; }
        .change-link { display:inline-flex; align-items:center; min-height:24px; margin-top:4px; padding:0 6px; border-radius:3px; background:#050505; color:#fff; font-size:10px; font-weight:950; text-transform:uppercase; }
        .ship-cost { margin-top:16px; font-size:14px; color:#111827; }
        .ship-cost strong { font-weight:950; }
        .comments { margin-top:12px; }
        .comments label { display:block; margin-bottom:5px; color:#4d5661; font-size:16px; font-weight:950; text-transform:uppercase; }
        .comments textarea { min-height:62px; padding:10px; resize:vertical; }
        .order-band { margin-top:18px; display:grid; grid-template-columns:minmax(0,1fr) minmax(280px, 1fr); background:#f1f1f1; border-top:1px solid #e3e6ea; border-bottom:1px solid #e3e6ea; }
        .order-lines { padding:20px 18px; border-right:1px solid #d7dce2; }
        .order-line { font-size:14px; line-height:1.55; color:#050505; }
        .order-line + .order-line { margin-top:8px; }
        .totals { padding:12px 28px; display:grid; align-content:center; gap:8px; }
        .total-row { display:flex; align-items:baseline; justify-content:center; gap:6px; font-size:16px; }
        .total-row strong { font-size:20px; color:#050505; }
        .total-row.final { font-weight:950; }
        .footer-actions { margin-top:30px; display:flex; justify-content:space-between; align-items:center; gap:16px; }
        .back-btn, .next-btn { min-height:62px; display:inline-flex; align-items:center; justify-content:center; border:0; padding:0 30px; color:#fff; font-size:13px; font-weight:950; text-transform:uppercase; }
        .back-btn { min-width:110px; background:#747f88; }
        .next-btn { min-width:216px; background:#00bf08; cursor:pointer; }
        .notice { margin:0 0 12px; padding:12px 14px; border:1px solid #f3c8c3; background:#fff0ee; color:#a33b32; font-size:13px; font-weight:850; }
        .tax-note { margin:8px 0 0; color:#6b7280; font-size:12px; font-weight:800; text-align:center; }
        @media (max-width:1100px) { .shipping-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .ship-card { border-bottom:1px solid #d7dce2; } .ship-card:nth-child(2) { border-right:0; } .customer-strip { grid-template-columns:repeat(2,minmax(0,1fr)); } }
        @media (max-width:720px) { .wrap { width:min(100% - 20px, 1376px); } .steps { grid-template-columns:1fr 1fr; gap:8px; } .step { min-height:42px; font-size:13px; } .step.is-active:before, .step.is-active:after { display:none; } .shipping-grid, .order-band, .customer-strip { grid-template-columns:1fr; } .ship-card { min-height:280px; border-right:0; } .order-lines { border-right:0; border-bottom:1px solid #d7dce2; } .footer-actions { display:grid; } .next-btn, .back-btn { width:100%; } }
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
    $method = old('fulfillment_method', 'delivery_akis');
    $shippingMethods = [
        [
            'key' => 'delivery_akis',
            'title' => $copy['ship_delivery_akis'] ?? 'Delivery with Akis Express',
            'address' => old('delivery_address') ?: '1 Main Street, 1000 City',
            'cost' => '€4,05',
            'icon' => 'truck-home',
        ],
        [
            'key' => 'pickup_akis',
            'title' => $copy['ship_pickup_akis'] ?? 'Pickup from an Akis express point',
            'address' => old('delivery_address') ?: '1 Main Street, 1000 City',
            'cost' => '€2,26',
            'icon' => 'fast-truck',
        ],
        [
            'key' => 'boxnow',
            'title' => $copy['ship_boxnow'] ?? 'BOX NOW Lockers | 24/7',
            'address' => '',
            'cost' => '€2,86',
            'icon' => 'truck-home',
        ],
        [
            'key' => 'pickup_main',
            'title' => $copy['ship_pickup_main'] ?? 'Pickup from Main Store',
            'address' => 'Main Store, 1 Main Street, 1000 City',
            'cost' => '',
            'icon' => 'store',
        ],
    ];
@endphp
@include('storefront.partials.site-header')
<main>
    <div class="wrap">
        @if($errors->any())
            <div class="notice">{{ $errors->first() }}</div>
        @endif
        @if(session('cart_error'))
            <div class="notice">{{ session('cart_error') }}</div>
        @endif

        <form class="checkout-form" method="post" action="{{ $checkoutUrl ?? ($localePrefix . '/checkout') }}">
            @csrf
            <nav class="steps" aria-label="Checkout steps">
                <a class="step" href="{{ $cartUrl ?? ($localePrefix . '/cart') }}">‹ {{ $copy['cart'] ?? 'Cart' }}</a>
                <span class="step is-active">{{ $copy['shipping'] ?? 'Shipping' }}</span>
                <span class="step">{{ $copy['payment'] ?? 'Payment' }}</span>
                <span class="step">{{ $copy['review'] ?? 'Review' }}</span>
            </nav>

            <section class="customer-strip" aria-label="Customer details">
                <div class="field">
                    <label for="customer_name">{{ $copy['name'] ?? 'Name' }}</label>
                    <input id="customer_name" name="customer_name" value="{{ old('customer_name', $user->name ?? '') }}" required>
                </div>
                <div class="field">
                    <label for="customer_email">{{ $copy['email'] ?? 'Email' }}</label>
                    <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', $user->email ?? '') }}" required>
                </div>
                <div class="field">
                    <label for="customer_phone">{{ $copy['phone'] ?? 'Phone' }}</label>
                    <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required>
                </div>
                <div class="field">
                    <label for="delivery_address">{{ $copy['delivery_address'] ?? 'Delivery address' }}</label>
                    <input id="delivery_address" name="delivery_address" value="{{ old('delivery_address') }}">
                </div>
                <div class="field">
                    <label for="city">{{ $copy['city'] ?? 'City' }}</label>
                    <input id="city" name="city" value="{{ old('city') }}">
                </div>
                <input type="hidden" name="postcode" value="{{ old('postcode') }}">
                <input type="hidden" name="company_name" value="{{ old('company_name', $user->company_name ?? '') }}">
                <input type="hidden" name="vat_number" value="{{ old('vat_number', $user->vat_number ?? '') }}">
            </section>

            <section class="shipping-grid" aria-label="{{ $copy['shipping'] ?? 'Shipping' }}">
                @foreach($shippingMethods as $shipping)
                    <label class="ship-card">
                        <input type="radio" name="fulfillment_method" value="{{ $shipping['key'] }}" @checked($method === $shipping['key'])>
                        <svg class="ship-icon" viewBox="0 0 180 130" aria-hidden="true">
                            @if($shipping['icon'] === 'store')
                                <path fill="currentColor" d="M35 50h110v12H35zM41 62h98v55H41zM50 74h24v32H50zm35 0h24v32H85zm35 0h11v32h-11zM43 42h94l8 18H35z"/>
                                <path fill="currentColor" d="M35 60c4 10 18 10 23 0 4 10 18 10 23 0 4 10 18 10 23 0 4 10 18 10 23 0 4 10 14 10 18 0H35z"/>
                            @else
                                <path fill="currentColor" d="M64 34h55l37 32h-28v50H64z"/>
                                <path fill="#fff" d="M86 63h26v34H86zM116 75h20l11 16v18h-31z"/>
                                <path fill="currentColor" d="M26 89h62v11H26zM38 76h44v6H38zM18 67h61v6H18zM47 56h37v6H47zM12 98h44v6H12z"/>
                                <circle cx="73" cy="112" r="9" fill="#fff"/><circle cx="73" cy="112" r="5" fill="currentColor"/>
                                <circle cx="139" cy="112" r="9" fill="#fff"/><circle cx="139" cy="112" r="5" fill="currentColor"/>
                            @endif
                        </svg>
                        <h2 class="ship-title">{{ $shipping['title'] }}</h2>
                        @if($shipping['address'])
                            <p class="ship-address">⌁ {{ $shipping['address'] }} <span class="change-link">{{ $copy['change'] ?? 'Change' }}</span></p>
                        @elseif($shipping['key'] === 'boxnow')
                            <span class="change-link">{{ $copy['change_point'] ?? 'Change point / Open map' }}</span>
                        @endif
                        @if($shipping['cost'])
                            <p class="ship-cost">{{ $copy['shipping_cost'] ?? 'Shipping cost' }}: <strong>{{ $shipping['cost'] }}</strong></p>
                        @endif
                    </label>
                @endforeach
            </section>

            <div class="comments">
                <label for="notes">{{ $copy['comments'] ?? 'Comments' }}</label>
                <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
            </div>

            <section class="order-band" aria-label="{{ $copy['order_summary'] ?? 'Order summary' }}">
                <div class="order-lines">
                    @foreach($items as $item)
                        <div class="order-line">{{ $item['quantity'] }} x {{ $item['title'] }} #{{ $item['display_code'] ?? $item['code'] ?? '' }} = {{ $item['line_total'] }}</div>
                    @endforeach
                </div>
                <div class="totals">
                    <div class="total-row"><span>{{ $copy['subtotal'] ?? 'Subtotal' }}:</span> <strong>{{ $subtotalFormatted }}</strong></div>
                    <div class="total-row final"><span>Total:</span> <strong>{{ $subtotalFormatted }}</strong></div>
                    <p class="tax-note">{{ $priceContext['note'] ?? '' }}</p>
                </div>
            </section>

            <div class="footer-actions">
                <a class="back-btn" href="{{ $cartUrl ?? ($localePrefix . '/cart') }}">‹ {{ $copy['back'] ?? 'Back' }}</a>
                <button class="next-btn" type="submit">{{ $copy['continue_next_step'] ?? 'Continue to next step' }}</button>
            </div>
        </form>
    </div>
</main>
@include('storefront.partials.site-footer')
@include('storefront.partials.site-header-script')
</body>
</html>
