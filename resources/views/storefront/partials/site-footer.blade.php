@once
    <style>
        .site-footer {
            margin-top:40px;
            background:#fff;
            border-top:1px solid #d9e0e8;
            color:#20242c;
        }
        .footer-main {
            padding:34px 0 28px;
        }
        .footer-grid {
            display:grid;
            grid-template-columns:minmax(260px, 1.45fr) repeat(2, minmax(150px, .72fr)) minmax(260px, 1.15fr);
            gap:28px;
            align-items:start;
        }
        .footer-brand {
            display:grid;
            gap:14px;
        }
        .footer-logo {
            width:190px;
            max-width:100%;
        }
        .footer-logo img {
            width:100%;
            height:auto;
            display:block;
        }
        .footer-copy {
            max-width:340px;
            margin:0;
            color:#667085;
            font-size:13px;
            line-height:1.6;
            font-weight:750;
        }
        .footer-contact {
            display:grid;
            gap:8px;
            margin:0;
            padding:0;
            list-style:none;
        }
        .footer-contact a,
        .footer-contact span {
            display:inline-flex;
            align-items:center;
            gap:8px;
            color:#253344;
            font-size:13px;
            font-weight:900;
        }
        .footer-title {
            margin:0 0 12px;
            color:#18212c;
            font-size:13px;
            font-weight:950;
            text-transform:uppercase;
            letter-spacing:1px;
        }
        .footer-links {
            display:grid;
            gap:9px;
            margin:0;
            padding:0;
            list-style:none;
        }
        .footer-links a {
            color:#4f5964;
            font-size:13px;
            font-weight:850;
            transition:color .16s ease;
        }
        .footer-links a:hover {
            color:#00a6a6;
        }
        .newsletter-form {
            display:grid;
            grid-template-columns:minmax(0,1fr) auto;
            gap:8px;
            margin-top:3px;
        }
        .newsletter-form input {
            min-width:0;
            min-height:42px;
            border:1px solid #cfd8e3;
            border-radius:8px;
            padding:0 12px;
            color:#20242c;
            font:inherit;
            font-size:13px;
            font-weight:800;
            outline:0;
        }
        .newsletter-form input:focus {
            border-color:#00a6a6;
            box-shadow:0 0 0 3px rgba(0,166,166,.12);
        }
        .newsletter-form button {
            min-height:42px;
            border:0;
            border-radius:8px;
            padding:0 14px;
            background:#00a6a6;
            color:#fff;
            font:inherit;
            font-size:12px;
            font-weight:950;
            cursor:pointer;
        }
        .footer-social-title {
            margin:16px 0 9px;
            color:#667085;
            font-size:12px;
            font-weight:900;
        }
        .footer-social {
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            margin:0;
            padding:0;
            list-style:none;
        }
        .footer-social a {
            width:36px;
            height:36px;
            display:grid;
            place-items:center;
            border-radius:8px;
            background:#18212c;
            color:#fff;
            font-size:12px;
            font-weight:950;
        }
        .footer-bottom {
            border-top:1px solid #e3e8ef;
            background:#f6f8fa;
        }
        .footer-bottom-row {
            min-height:66px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:18px;
        }
        .footer-credit {
            margin:0;
            color:#667085;
            font-size:12px;
            font-weight:800;
        }
        .payment-badges {
            display:flex;
            flex-wrap:wrap;
            justify-content:flex-end;
            gap:8px;
        }
        .payment-badge {
            min-height:34px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            border:1px solid #d9e0e8;
            border-radius:8px;
            padding:0 10px;
            background:#fff;
            color:#253344;
            font-size:11px;
            font-weight:950;
            letter-spacing:.3px;
        }
        @media (max-width:980px) {
            .footer-grid {
                grid-template-columns:1fr 1fr;
            }
        }
        @media (max-width:620px) {
            .site-footer {
                margin-top:28px;
            }
            .footer-grid,
            .newsletter-form {
                grid-template-columns:1fr;
            }
            .footer-bottom-row {
                align-items:flex-start;
                flex-direction:column;
                padding:16px 0;
            }
            .payment-badges {
                justify-content:flex-start;
            }
        }
    </style>
@endonce

@php
    $footerHome = $homeUrl ?? ('/' . ($locale ?? 'en'));
    $footerYear = now()->year;
@endphp

<footer class="site-footer">
    <section class="footer-main">
        <div class="wrap">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a class="footer-logo" href="{{ $footerHome }}" aria-label="Home">
                        <img src="/images/brand/logo.svg" alt="{{ config('app.name') }}">
                    </a>
                    <p class="footer-copy">{{ $copy['footer_intro'] ?? 'We provide technology products, business equipment and support for home and business customers.' }}</p>
                    <ul class="footer-contact">
                        <li><a href="tel:+10000000000">☎ +1 000 000 0000</a></li>
                        <li><a href="mailto:info@example.com">✉ info@example.com</a></li>
                    </ul>
                </div>

                <nav aria-label="Footer order links">
                    <h2 class="footer-title">{{ $copy['footer_order'] ?? 'Order' }}</h2>
                    <ul class="footer-links">
                        <li><a href="{{ $shippingUrl ?? (($localePrefix ?? '/en') . '/shipping') }}">{{ $copy['footer_shipping'] ?? 'Shipping' }}</a></li>
                        <li><a href="{{ $cartUrl ?? (($localePrefix ?? '/en') . '/cart') }}">{{ $copy['cart'] ?? 'Cart' }}</a></li>
                        <li><a href="{{ $checkoutUrl ?? (($localePrefix ?? '/en') . '/checkout') }}">{{ $copy['checkout'] ?? 'Checkout' }}</a></li>
                    </ul>
                </nav>

                <nav aria-label="Footer information links">
                    <h2 class="footer-title">{{ $copy['footer_info'] ?? 'Info' }}</h2>
                    <ul class="footer-links">
                        <li><a href="{{ $faqUrl ?? (($localePrefix ?? '/en') . '/faq') }}">{{ $copy['footer_faq'] ?? 'F.A.Q.' }}</a></li>
                        <li><a href="{{ $cookiePolicyUrl ?? (($localePrefix ?? '/en') . '/cookie-policy') }}">{{ $copy['footer_cookie'] ?? 'Cookie Policy' }}</a></li>
                        <li><a href="{{ $privacyPolicyUrl ?? (($localePrefix ?? '/en') . '/privacy-policy') }}">{{ $copy['footer_privacy'] ?? 'Privacy Policy' }}</a></li>
                        <li><a href="{{ $termsUrl ?? (($localePrefix ?? '/en') . '/terms-and-conditions') }}">{{ $copy['footer_terms'] ?? 'Terms and Conditions' }}</a></li>
                    </ul>
                </nav>

                <div>
                    <h2 class="footer-title">{{ $copy['footer_follow'] ?? 'Follow us' }}</h2>
                    <form class="newsletter-form" action="#" method="post">
                        <input type="email" name="user_email" placeholder="{{ $copy['email'] ?? 'Email' }}" aria-label="{{ $copy['email'] ?? 'Email' }}">
                        <button type="submit" name="add_user">{{ $copy['footer_subscribe'] ?? 'Subscribe' }}</button>
                    </form>
                    <p class="footer-social-title">{{ $copy['footer_social'] ?? 'Find us social' }}</p>
                    <ul class="footer-social">
                        <li><a href="https://www.facebook.com/" target="_blank" rel="noopener" aria-label="Facebook">f</a></li>
                        <li><a href="https://www.linkedin.com/" target="_blank" rel="noopener" aria-label="LinkedIn">in</a></li>
                        <li><a href="https://www.instagram.com/" target="_blank" rel="noopener" aria-label="Instagram">ig</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="footer-bottom">
        <div class="wrap footer-bottom-row">
            <p class="footer-credit">© {{ $footerYear }} {{ config('app.name') }}</p>
            <div class="payment-badges" aria-label="Payment methods">
                <span class="payment-badge">Stripe</span>
                <span class="payment-badge">JCC</span>
                <span class="payment-badge">VISA</span>
                <span class="payment-badge">Mastercard</span>
                <span class="payment-badge">3D Secure</span>
            </div>
        </div>
    </section>
</footer>
