<!DOCTYPE html>
<html lang="{{ $htmlLang ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['cart_title'] ?? 'Your cart' }} | {{ config('app.name') }}</title>
    @include('storefront.partials.site-fonts')
    <style>
        :root { --ink:#20242c; --muted:#667085; --line:#d9e0e8; --soft:#f3f6f8; --dark:#18212c; --green:#00a6a6; --red:#c2413a; --radius:8px; }
        @include('storefront.partials.site-font-styles')
        * { box-sizing:border-box; }
        body { margin:0; font-family:var(--font-sans); background:#eef2f5; color:var(--ink); letter-spacing:0; }
        a { color:inherit; text-decoration:none; }
        img { display:block; max-width:100%; }
        button, input { font:inherit; }
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
        main { padding:24px 0 52px; }
        .hero { padding:24px; border-radius:var(--radius); background:linear-gradient(135deg,#18212c,#223242 55%,#102532); color:#fff; }
        .hero small { color:#7dd3fc; font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:1.5px; }
        .hero h1 { margin:8px 0 0; font-size:34px; line-height:1.1; }
        .notice { margin-top:14px; padding:12px 14px; border-radius:var(--radius); border:1px solid #b7e2d7; background:#effaf6; color:#0d6f4f; font-size:13px; font-weight:850; }
        .notice.error { border-color:#f3c8c3; background:#fff0ee; color:#a33b32; }
        .cart-layout { margin-top:18px; display:grid; grid-template-columns:minmax(0, 1fr) 320px; gap:18px; align-items:start; }
        .panel, .summary { border:1px solid var(--line); border-radius:var(--radius); background:#fff; }
        .panel { overflow:hidden; }
        .cart-row { display:grid; grid-template-columns:92px minmax(0, 1fr) 138px 96px; gap:14px; align-items:center; padding:14px; border-bottom:1px solid var(--line); }
        .cart-row:last-child { border-bottom:0; }
        .thumb { aspect-ratio:1; display:grid; place-items:center; border:1px solid var(--line); border-radius:var(--radius); background:#f8fafb; overflow:hidden; }
        .thumb img { width:100%; height:100%; object-fit:contain; padding:8px; }
        .thumb span { width:42px; height:42px; display:grid; place-items:center; border-radius:13px; background:#eaf1ff; color:#2f6fed; font-weight:900; }
        .item-title { display:block; font-size:14px; line-height:1.4; font-weight:900; }
        .item-meta { margin-top:7px; display:flex; flex-wrap:wrap; gap:6px; }
        .item-meta span { min-height:24px; display:inline-flex; align-items:center; padding:0 8px; border-radius:999px; background:#f0f4f7; color:#637180; font-size:10px; font-weight:850; }
        .qty-form { display:grid; gap:8px; }
        .qty-control { display:grid; gap:6px; }
        .qty-control input { width:72px; height:38px; border:1px solid var(--line); border-radius:var(--radius); padding:0 8px; font-weight:900; text-align:center; }
        .checkout-btn { min-height:36px; display:inline-flex; align-items:center; justify-content:center; border:0; border-radius:var(--radius); padding:0 11px; font-size:11px; font-weight:900; cursor:pointer; }
        .line-price { text-align:right; }
        .line-price strong { display:block; color:var(--red); font-size:18px; }
        .line-price span { display:block; margin-top:4px; color:var(--muted); font-size:11px; font-weight:800; }
        .summary { padding:18px; position:sticky; top:88px; }
        .summary h2 { margin:0 0 14px; font-size:18px; }
        .summary-line { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-top:1px solid var(--line); font-size:13px; font-weight:850; }
        .summary-line strong { color:var(--red); font-size:22px; }
        .checkout-btn { width:100%; min-height:44px; background:#00a6a6; color:#fff; margin-top:10px; }
        .continue { width:100%; min-height:42px; margin-top:8px; display:flex; align-items:center; justify-content:center; border:1px solid var(--line); border-radius:var(--radius); font-size:12px; font-weight:900; }
        .empty { margin-top:18px; padding:42px 20px; border:1px solid var(--line); border-radius:var(--radius); background:#fff; text-align:center; }
        .empty h2 { margin:0; font-size:24px; }
        .empty p { margin:10px auto 18px; max-width:440px; color:var(--muted); font-size:14px; line-height:1.6; font-weight:700; }
        .cart-modal { position:fixed; inset:0; z-index:1000; display:none; align-items:center; justify-content:center; padding:18px; background:rgba(24,33,44,.62); }
        .cart-modal.is-open { display:flex; }
        .cart-modal-card { width:min(430px, 100%); border:1px solid var(--line); border-radius:var(--radius); background:#fff; box-shadow:0 22px 60px rgba(24,33,44,.28); overflow:hidden; }
        .cart-modal-head { padding:18px 20px; background:#18212c; color:#fff; }
        .cart-modal-head small { display:block; color:#7dd3fc; font-size:10px; font-weight:950; text-transform:uppercase; letter-spacing:1.4px; }
        .cart-modal-head strong { display:block; margin-top:6px; font-size:20px; line-height:1.2; }
        .cart-modal-body { padding:18px 20px; }
        .cart-modal-body p { margin:0; color:#4f5964; font-size:14px; line-height:1.55; font-weight:750; }
        .cart-modal-actions { display:flex; justify-content:flex-end; gap:10px; padding:0 20px 20px; }
        .modal-btn { min-height:40px; display:inline-flex; align-items:center; justify-content:center; border:0; border-radius:var(--radius); padding:0 14px; font-size:12px; font-weight:950; cursor:pointer; }
        .modal-btn.cancel { background:#eef2f5; color:#18212c; }
        .modal-btn.remove { background:#c2413a; color:#fff; }
        @media (max-width:880px) { .cart-layout { grid-template-columns:1fr; } .summary { position:static; } .cart-row { grid-template-columns:74px minmax(0, 1fr); } .qty-form, .line-price { grid-column:2; text-align:left; } }
        @media (max-width:520px) { .wrap { width:min(100% - 20px, 1180px); } .header .wrap { display:grid; grid-template-columns:1fr; padding:12px 0; } .header-actions { flex-wrap:wrap; } }
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
    $removeConfirm = $copy['cart_remove_confirm'] ?? 'Are you sure you want to remove this item from your cart?';
    $removeTitle = $copy['cart_remove_title'] ?? 'Remove item?';
    $removeCancel = $copy['cancel'] ?? 'Cancel';
    $removeAction = $copy['remove'] ?? 'Remove';
@endphp
@include('storefront.partials.site-header')
<main>
    <div class="wrap">
        <section class="hero">
            <small>{{ $copy['cart'] ?? 'Cart' }}</small>
            <h1>{{ $copy['cart_title'] ?? 'Your cart' }}</h1>
        </section>

        @if(session('cart_status'))
            <div class="notice">{{ session('cart_status') }}</div>
        @endif

        @if(session('cart_error'))
            <div class="notice error">{{ session('cart_error') }}</div>
        @endif

        @if($items)
            <div class="cart-layout">
                <section class="panel">
                    @foreach($items as $item)
                        <article class="cart-row">
                            <a class="thumb" href="{{ $item['url'] }}">
                                @if($item['image'])
                                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] }}">
                                @else
                                    <span>{{ mb_substr($item['brand'] ?: $item['title'], 0, 1) }}</span>
                                @endif
                            </a>

                            <div>
                                <a class="item-title" href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                                <div class="item-meta">
                                    @if($item['display_code'] ?? $item['code'] ?? '')<span>{{ $item['display_code'] ?? $item['code'] }}</span>@endif
                                    @if($item['brand'])<span>{{ $item['brand'] }}</span>@endif
                                    @if($item['mpn'])<span>{{ $item['mpn'] }}</span>@endif
                                </div>
                            </div>

                            <div class="qty-form">
                                <form class="qty-control js-qty-form" method="post" action="{{ $item['update_url'] }}">
                                    @csrf
                                    @method('PATCH')
                                    <input class="js-cart-qty" type="number" name="quantity" min="0" max="99" value="{{ $item['quantity'] }}" data-current="{{ $item['quantity'] }}" aria-label="{{ $copy['quantity'] ?? 'Quantity' }}">
                                </form>
                            </div>

                            <div class="line-price">
                                <strong>{{ $item['line_total'] }}</strong>
                                <span>{{ $item['price'] }} × {{ $item['quantity'] }}</span>
                            </div>
                        </article>
                    @endforeach
                </section>

                <aside class="summary">
                    <h2>{{ $copy['order_summary'] ?? 'Order summary' }}</h2>
                    <div class="summary-line">
                        <span>{{ $copy['subtotal'] ?? 'Subtotal' }}</span>
                        <strong>{{ $subtotalFormatted }}</strong>
                    </div>
                    <p style="margin:0 0 8px;color:var(--muted);font-size:12px;font-weight:800;line-height:1.45;">{{ $priceContext['note'] ?? ($copy['tax_note_retail'] ?? '') }}</p>
                    <a class="checkout-btn" href="{{ $checkoutUrl ?? ($localePrefix . '/checkout') }}">{{ $copy['checkout'] ?? 'Checkout' }}</a>
                    <a class="continue" href="{{ $homeUrl }}">{{ $copy['continue_shopping'] ?? 'Continue shopping' }}</a>
                </aside>
            </div>
        @else
            <section class="empty">
                <h2>{{ $copy['cart_empty'] ?? 'Your cart is empty.' }}</h2>
                <p>{{ $copy['cart_empty_hint'] ?? 'Browse the catalog and add products for a quote or order.' }}</p>
                <a class="checkout-btn" href="{{ $homeUrl }}">{{ $copy['continue_shopping'] ?? 'Continue shopping' }}</a>
            </section>
        @endif
    </div>
</main>
@include('storefront.partials.site-footer')
<div class="cart-modal" data-cart-remove-modal aria-hidden="true">
    <div class="cart-modal-card" role="dialog" aria-modal="true" aria-labelledby="cart-remove-title">
        <div class="cart-modal-head">
            <small>{{ $copy['cart'] ?? 'Cart' }}</small>
            <strong id="cart-remove-title">{{ $removeTitle }}</strong>
        </div>
        <div class="cart-modal-body">
            <p>{{ $removeConfirm }}</p>
        </div>
        <div class="cart-modal-actions">
            <button class="modal-btn cancel" type="button" data-cart-remove-cancel>{{ $removeCancel }}</button>
            <button class="modal-btn remove" type="button" data-cart-remove-confirm>{{ $removeAction }}</button>
        </div>
    </div>
</div>
@include('storefront.partials.site-header-script')
<script>
    const removeModal = document.querySelector('[data-cart-remove-modal]');
    const removeConfirmButton = document.querySelector('[data-cart-remove-confirm]');
    const removeCancelButton = document.querySelector('[data-cart-remove-cancel]');
    let pendingRemoveInput = null;
    let pendingRemoveForm = null;
    let pendingPreviousQuantity = 1;

    const closeRemoveModal = () => {
        if (removeModal) {
            removeModal.classList.remove('is-open');
            removeModal.setAttribute('aria-hidden', 'true');
        }
    };

    const resetPendingRemove = () => {
        if (pendingRemoveInput) {
            pendingRemoveInput.value = pendingPreviousQuantity || 1;
        }

        pendingRemoveInput = null;
        pendingRemoveForm = null;
        pendingPreviousQuantity = 1;
        closeRemoveModal();
    };

    const openRemoveModal = (input, form, previous) => {
        pendingRemoveInput = input;
        pendingRemoveForm = form;
        pendingPreviousQuantity = previous;

        if (removeModal) {
            removeModal.classList.add('is-open');
            removeModal.setAttribute('aria-hidden', 'false');
        }
    };

    removeCancelButton?.addEventListener('click', resetPendingRemove);
    removeModal?.addEventListener('click', (event) => {
        if (event.target === removeModal) {
            resetPendingRemove();
        }
    });
    removeConfirmButton?.addEventListener('click', () => {
        if (pendingRemoveInput && pendingRemoveForm) {
            pendingRemoveInput.value = 0;
            pendingRemoveForm.submit();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && removeModal?.classList.contains('is-open')) {
            resetPendingRemove();
        }
    });

    document.querySelectorAll('.js-cart-qty').forEach((input) => {
        let timer;

        input.addEventListener('change', () => {
            const form = input.closest('form');
            const previous = Math.max(1, parseInt(input.dataset.current || '1', 10));
            const next = Math.max(0, Math.min(99, parseInt(input.value || '0', 10)));

            input.value = next;

            if (next === previous) {
                return;
            }

            if (next === 0) {
                openRemoveModal(input, form, previous || 1);
                return;
            }

            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 180);
        });
    });
</script>
</body>
</html>
