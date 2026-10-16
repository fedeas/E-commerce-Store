<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    protected ?bool $databaseReachable = null;

    public function home(?string $locale = null): View
    {
        $locale = $this->setStorefrontLocale($locale);

        return view('storefront.home', [
            'banners' => $this->banners(),
            'categories' => $this->categories(),
            'offers' => $this->offers(),
            'featuredSection' => $this->featuredSection(),
            'featuredProducts' => $this->featuredProducts(),
            'latestProducts' => $this->latestProducts(),
            'imageProducts' => $this->imageProducts(),
            'topBrands' => $this->topBrands(),
            'catalogStats' => $this->catalogStats(),
        ] + $this->storefrontContext($locale, 'home'));
    }

    public function category(Request $request, string $localeOrSlug, ?string $slug = null): View
    {
        $locale = $slug === null ? null : $localeOrSlug;
        $slug = $slug ?? $localeOrSlug;
        $locale = $this->setStorefrontLocale($locale);
        $categories = $this->categories();
        $category = $this->findCategoryInTree($categories, $slug) ?? $this->categoryBySlug($slug);
        $productResults = $this->productsForCategory($slug, $category, $request);
        $products = $productResults['items'];
        $categoryPath = $category['url_path'] ?? $slug;

        return view('storefront.catalog', [
            'mode' => 'category',
            'title' => $category['name'] ?? $this->copy($locale, 'category'),
            'subtitle' => $category
                ? $productResults['total'] . ' ' . $this->copy($locale, 'products_available')
                : $this->copy($locale, 'category_empty'),
            'categories' => $categories,
            'currentCategory' => $category,
            'products' => $products,
            'productTotal' => $productResults['total'],
            'pagination' => $productResults['pagination'],
            'extraFilters' => $productResults['filters'],
            'selectedFilters' => $productResults['selectedFilters'],
            'query' => '',
        ] + $this->storefrontContext($locale, 'category', ['slug' => $categoryPath, 'page' => $productResults['pagination']['page']]));
    }

    public function search(Request $request, ?string $locale = null): View
    {
        $locale = $this->setStorefrontLocale($locale);
        $query = trim((string) $request->query('q', ''));
        $productResults = $this->searchProducts($query, $request);

        return view('storefront.catalog', [
            'mode' => 'search',
            'title' => $query !== '' ? $this->copy($locale, 'search') . ': ' . $query : $this->copy($locale, 'search_products'),
            'subtitle' => $query !== '' ? $this->copy($locale, 'search_results_hint') : $this->copy($locale, 'search_empty_hint'),
            'categories' => $this->categories(),
            'currentCategory' => null,
            'products' => $productResults['items'],
            'productTotal' => $productResults['total'],
            'pagination' => $productResults['pagination'],
            'extraFilters' => $productResults['filters'],
            'selectedFilters' => $productResults['selectedFilters'],
            'query' => $query,
        ] + $this->storefrontContext($locale, 'search', ['q' => $query, 'page' => $productResults['pagination']['page']]));
    }

    public function outlet(Request $request, ?string $locale = null): View
    {
        $locale = $this->setStorefrontLocale($locale);
        $productResults = $this->specialProducts('outlet', $request);

        return view('storefront.catalog', [
            'mode' => 'outlet',
            'title' => $this->copy($locale, 'outlet'),
            'subtitle' => $this->copy($locale, 'outlet_hint'),
            'categories' => $this->categories(),
            'currentCategory' => null,
            'products' => $productResults['items'],
            'productTotal' => $productResults['total'],
            'pagination' => $productResults['pagination'],
            'extraFilters' => $productResults['filters'],
            'selectedFilters' => $productResults['selectedFilters'],
            'query' => '',
        ] + $this->storefrontContext($locale, 'outlet', ['page' => $productResults['pagination']['page']]));
    }

    public function bestSellers(Request $request, ?string $locale = null): View
    {
        $locale = $this->setStorefrontLocale($locale);
        $productResults = $this->specialProducts('best_sellers', $request);

        return view('storefront.catalog', [
            'mode' => 'best_sellers',
            'title' => $this->copy($locale, 'best_sellers'),
            'subtitle' => $this->copy($locale, 'best_sellers_hint'),
            'categories' => $this->categories(),
            'currentCategory' => null,
            'products' => $productResults['items'],
            'productTotal' => $productResults['total'],
            'pagination' => $productResults['pagination'],
            'extraFilters' => $productResults['filters'],
            'selectedFilters' => $productResults['selectedFilters'],
            'query' => '',
        ] + $this->storefrontContext($locale, 'best_sellers', ['page' => $productResults['pagination']['page']]));
    }

    public function product(string $localeOrSlug, ?string $slug = null): View
    {
        $locale = $slug === null ? null : $localeOrSlug;
        $slug = $slug ?? $localeOrSlug;
        $locale = $this->setStorefrontLocale($locale);

        return view('storefront.product', [
            'categories' => $this->categories(),
            'product' => $this->productBySlug($slug),
        ] + $this->storefrontContext($locale, 'product', ['slug' => $slug]));
    }

    public function cart(?string $locale = null): View
    {
        $locale = $this->setStorefrontLocale($locale);
        /** @var Request $request */
        $request = request();
        $summary = $this->cartSummary($request);

        return view('storefront.cart', [
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'subtotalFormatted' => $this->money($summary['subtotal']),
            'categories' => $this->categories(),
        ] + $this->storefrontContext($locale, 'cart'));
    }

    public function checkout(?string $locale = null): View|RedirectResponse
    {
        $locale = $this->setStorefrontLocale($locale);
        /** @var Request $request */
        $request = request();
        $summary = $this->cartSummary($request);

        if (($summary['items'] ?? []) === []) {
            return Redirect::to($this->localizedPath($locale, 'cart', []))
                ->with('cart_error', $this->copy($locale, 'checkout_empty'));
        }

        return view('storefront.checkout', [
            'items' => $summary['items'],
            'subtotal' => $summary['subtotal'],
            'subtotalFormatted' => $this->money($summary['subtotal']),
            'stores' => $this->contactStores(),
            'categories' => $this->categories(),
            'user' => $request->user(),
        ] + $this->storefrontContext($locale, 'checkout'));
    }

    public function submitCheckout(Request $request, ?string $locale = null): RedirectResponse
    {
        $locale = $this->setStorefrontLocale($locale);
        $summary = $this->cartSummary($request);
        $items = $summary['items'] ?? [];

        if ($items === []) {
            return Redirect::to($this->localizedPath($locale, 'cart', []))
                ->with('cart_error', $this->copy($locale, 'checkout_empty'));
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:160'],
            'customer_email' => ['required', 'email', 'max:190'],
            'customer_phone' => ['required', 'string', 'max:80'],
            'company_name' => ['nullable', 'string', 'max:190'],
            'vat_number' => ['nullable', 'string', 'max:80'],
            'fulfillment_method' => ['required', 'in:delivery_akis,pickup_akis,boxnow,pickup_main,pickup_city'],
            'pickup_store' => ['nullable', 'string', 'max:40'],
            'delivery_address' => ['nullable', 'required_unless:fulfillment_method,pickup_main,pickup_city', 'string', 'max:220'],
            'city' => ['nullable', 'required_unless:fulfillment_method,pickup_main,pickup_city', 'string', 'max:120'],
            'postcode' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'customer_name.required' => $this->copy($locale, 'checkout_name_required'),
            'customer_email.required' => $this->copy($locale, 'contact_email_required'),
            'customer_email.email' => $this->copy($locale, 'contact_email_invalid'),
            'customer_phone.required' => $this->copy($locale, 'checkout_phone_required'),
            'delivery_address.required_unless' => $this->copy($locale, 'checkout_delivery_required'),
            'city.required_unless' => $this->copy($locale, 'checkout_city_required'),
        ]);

        if (! $this->tableReady('storefront_orders') || ! $this->tableReady('storefront_order_items')) {
            return Redirect::back()
                ->withInput()
                ->with('cart_error', $this->copy($locale, 'checkout_tables_missing'));
        }

        $orderNumber = $this->nextOrderNumber();
        $priceContext = $this->priceContext();

        try {
            DB::transaction(function () use ($request, $data, $items, $summary, $locale, $orderNumber, $priceContext): void {
                $orderId = DB::table('storefront_orders')->insertGetId([
                    'user_id' => $request->user()?->id,
                    'order_number' => $orderNumber,
                    'status' => 'new',
                    'locale' => $locale,
                    'customer_type' => $priceContext['group'] ?? 'retail',
                    'customer_name' => $data['customer_name'],
                    'customer_email' => $data['customer_email'],
                    'customer_phone' => $data['customer_phone'],
                    'company_name' => $data['company_name'] ?? null,
                    'vat_number' => $data['vat_number'] ?? null,
                    'fulfillment_method' => $data['fulfillment_method'],
                    'pickup_store' => str_starts_with($data['fulfillment_method'], 'pickup_') ? $data['fulfillment_method'] : null,
                    'delivery_address' => ! str_starts_with($data['fulfillment_method'], 'pickup_') ? ($data['delivery_address'] ?? null) : null,
                    'city' => ! str_starts_with($data['fulfillment_method'], 'pickup_') ? ($data['city'] ?? null) : null,
                    'postcode' => $data['postcode'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'subtotal' => round((float) ($summary['subtotal'] ?? 0), 2),
                    'price_note' => $priceContext['note'] ?? null,
                    'meta' => json_encode(['source' => 'storefront'], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($items as $item) {
                    DB::table('storefront_order_items')->insert([
                        'storefront_order_id' => $orderId,
                        'product_id' => (int) ($item['id'] ?? 0) ?: null,
                        'product_title' => (string) ($item['title'] ?? 'Product'),
                        'product_code' => (string) ($item['code'] ?? ''),
                        'display_code' => (string) ($item['display_code'] ?? $item['code'] ?? ''),
                        'brand' => (string) ($item['brand'] ?? ''),
                        'mpn' => (string) ($item['mpn'] ?? ''),
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'unit_price' => round((float) ($item['unit_price_number'] ?? 0), 2),
                        'line_total' => round(((float) ($item['unit_price_number'] ?? 0)) * ((int) ($item['quantity'] ?? 1)), 2),
                        'meta' => json_encode(['url' => $item['url'] ?? ''], JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            $this->mailCheckoutRequest($orderNumber, $data, $items, (float) ($summary['subtotal'] ?? 0), $priceContext);
            $request->session()->forget('cart.items');

            return Redirect::to($this->localizedPath($locale, 'cart', []))
                ->with('cart_status', $this->copy($locale, 'checkout_sent') . ' #' . $orderNumber);
        } catch (\Throwable) {
            return Redirect::back()
                ->withInput()
                ->with('cart_error', $this->copy($locale, 'checkout_failed'));
        }
    }

    public function contact(?string $locale = null): View
    {
        $locale = $this->setStorefrontLocale($locale);

        return view('storefront.contact', [
            'categories' => $this->categories(),
            'stores' => $this->contactStores(),
        ] + $this->storefrontContext($locale, 'contact'));
    }

    public function shipping(?string $locale = null): View
    {
        return $this->contentPageFor($locale, 'shipping');
    }

    public function faq(?string $locale = null): View
    {
        return $this->contentPageFor($locale, 'faq');
    }

    public function cookiePolicy(?string $locale = null): View
    {
        return $this->contentPageFor($locale, 'cookie-policy');
    }

    public function privacyPolicy(?string $locale = null): View
    {
        return $this->contentPageFor($locale, 'privacy-policy');
    }

    public function termsAndConditions(?string $locale = null): View
    {
        return $this->contentPageFor($locale, 'terms-and-conditions');
    }

    public function contentPage(string $localeOrSlug, ?string $slug = null): View
    {
        $locale = $slug === null ? null : $localeOrSlug;
        $slug = $slug ?? $localeOrSlug;

        return $this->contentPageFor($locale, $slug);
    }

    public function sendContact(Request $request, ?string $locale = null): RedirectResponse
    {
        $locale = $this->setStorefrontLocale($locale);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:80'],
            'store' => ['nullable', 'string', 'max:80'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ], [
            'name.required' => $this->copy($locale, 'contact_name_required'),
            'email.required' => $this->copy($locale, 'contact_email_required'),
            'email.email' => $this->copy($locale, 'contact_email_invalid'),
            'message.required' => $this->copy($locale, 'contact_message_required'),
            'message.min' => $this->copy($locale, 'contact_message_required'),
        ]);

        $recipient = (string) config('mail.contact_to', 'sales@example.com');
        $subject = 'Contact request from ' . $data['name'];
        $body = implode("\n", [
            'New contact request from the storefront.',
            '',
            'Name: ' . $data['name'],
            'Email: ' . $data['email'],
            'Phone: ' . ($data['phone'] ?: '-'),
            'Preferred store: ' . ($data['store'] ?: '-'),
            '',
            'Message:',
            $data['message'],
        ]);

        Mail::raw($body, function ($message) use ($data, $recipient, $subject): void {
            $message
                ->to($recipient)
                ->replyTo($data['email'], $data['name'])
                ->subject($subject);
        });

        return Redirect::to($this->localizedPath($locale, 'contact', []))
            ->with('status', $this->copy($locale, 'contact_sent'));
    }

    public function b2bRequest(?string $locale = null): View
    {
        $locale = $this->setStorefrontLocale($locale);

        return view('storefront.b2b-request', [
            'categories' => $this->categories(),
            'user' => request()->user(),
        ] + $this->storefrontContext($locale, 'b2b_request'));
    }

    public function submitB2bRequest(Request $request, ?string $locale = null): RedirectResponse
    {
        $locale = $this->setStorefrontLocale($locale);
        $user = $request->user();

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:24'],
            'phone' => ['nullable', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:255'],
            'customer_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($user->isB2b()) {
            return Redirect::to('/' . $locale)->with('cart_status', $this->copy($locale, 'b2b_already_active'));
        }

        $user->forceFill($data + [
            'b2b_requested_at' => now(),
            'b2b_approved_at' => null,
            'b2b_rejected_at' => null,
        ])->save();

        return Redirect::to($this->localizedPath($locale, 'b2b_request', []))
            ->with('status', $this->copy($locale, 'b2b_request_sent'));
    }

    public function publicMedia(string $path): BinaryFileResponse
    {
        abort_if(str_contains($path, '..'), 404);

        $file = storage_path('app/public/' . ltrim($path, '/'));

        abort_unless(is_file($file), 404);

        return response()->file($file);
    }

    public function addToCart(Request $request, ?string $locale = null): RedirectResponse
    {
        $this->setStorefrontLocale($locale);
        $productId = max(0, (int) $request->input('product_id'));
        $quantity = max(1, min(99, (int) $request->input('quantity', 1)));

        if ($productId <= 0 || ! $this->cartProductExists($productId)) {
            return Redirect::back()->with('cart_error', $this->copy(App::getLocale(), 'cart_unavailable'));
        }

        $items = $request->session()->get('cart.items', []);
        $items[$productId] = min(99, ((int) ($items[$productId] ?? 0)) + $quantity);
        $request->session()->put('cart.items', $items);

        return Redirect::back()->with('cart_status', $this->copy(App::getLocale(), 'cart_added'));
    }

    public function updateCartItem(Request $request, string $localeOrProduct, ?string $product = null): RedirectResponse
    {
        $locale = $product === null ? null : $localeOrProduct;
        $productId = (int) ($product ?? $localeOrProduct);
        $this->setStorefrontLocale($locale);
        $quantity = max(0, min(99, (int) $request->input('quantity', 1)));
        $items = $request->session()->get('cart.items', []);

        if ($quantity <= 0) {
            unset($items[$productId]);
        } elseif (isset($items[$productId])) {
            $items[$productId] = $quantity;
        }

        $request->session()->put('cart.items', $items);

        return Redirect::back()->with('cart_status', $this->copy(App::getLocale(), 'cart_updated'));
    }

    public function removeCartItem(Request $request, string $localeOrProduct, ?string $product = null): RedirectResponse
    {
        $locale = $product === null ? null : $localeOrProduct;
        $productId = (int) ($product ?? $localeOrProduct);
        $this->setStorefrontLocale($locale);
        $items = $request->session()->get('cart.items', []);
        unset($items[$productId]);
        $request->session()->put('cart.items', $items);

        return Redirect::back()->with('cart_status', $this->copy(App::getLocale(), 'cart_removed'));
    }

    public function sharedStorefrontData(?string $locale, string $page, array $params = []): array
    {
        $locale = $this->setStorefrontLocale($locale);

        return [
            'categories' => $this->categories(),
        ] + $this->storefrontContext($locale, $page, $params);
    }

    public function setStorefrontLocale(?string $locale): string
    {
        $locale = in_array($locale, ['en', 'el'], true) ? $locale : 'en';
        App::setLocale($locale);

        return $locale;
    }

    protected function contentPageFor(?string $locale, string $slug): View
    {
        $locale = $this->setStorefrontLocale($locale);
        $slug = [
            'tropoi-apostolis' => 'shipping',
            'syxnes-erwtiseis' => 'faq',
            'politiki-cookies' => 'cookie-policy',
            'politiki-aporritoy' => 'privacy-policy',
            'oroi-kai-proypotheseis' => 'terms-and-conditions',
        ][$slug] ?? $slug;
        $pages = $this->contentPageMeta($locale);

        abort_unless(isset($pages[$slug]), 404);

        return view('storefront.content-page', [
            'categories' => $this->categories(),
            'page' => $pages[$slug],
        ] + $this->storefrontContext($locale, $slug));
    }

    protected function contentPageMeta(string $locale): array
    {
        $english = [
            'shipping' => [
                'slug' => 'shipping',
                'title' => 'Shipping',
                'eyebrow' => 'Order information',
                'intro' => 'Delivery, pickup and dispatch information for orders placed in the store.',
            ],
            'faq' => [
                'slug' => 'faq',
                'title' => 'F.A.Q.',
                'eyebrow' => 'Help center',
                'intro' => 'Common questions about our products, services, support and business solutions.',
            ],
            'cookie-policy' => [
                'slug' => 'cookie-policy',
                'title' => 'Cookie Policy',
                'eyebrow' => 'Privacy information',
                'intro' => 'How cookies are used for site functionality, analytics, preferences and advertising.',
            ],
            'privacy-policy' => [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'eyebrow' => 'Privacy information',
                'intro' => 'How we process personal data for the e-shop and digital services.',
            ],
            'terms-and-conditions' => [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'eyebrow' => 'Legal information',
                'intro' => 'Terms governing use of the e-shop, orders, payments, delivery, returns and warranties.',
            ],
        ];

        if ($locale !== 'el') {
            return $english;
        }

        return [
            'shipping' => array_replace($english['shipping'], [
                'title' => 'Αποστολή',
                'eyebrow' => 'Πληροφορίες παραγγελίας',
                'intro' => 'Πληροφορίες παράδοσης, παραλαβής και αποστολής για τις παραγγελίες του καταστήματος.',
            ]),
            'faq' => array_replace($english['faq'], [
                'title' => 'Συχνές ερωτήσεις',
                'eyebrow' => 'Κέντρο βοήθειας',
                'intro' => 'Συχνές ερωτήσεις για προϊόντα, υπηρεσίες, υποστήριξη και επαγγελματικές λύσεις.',
            ]),
            'cookie-policy' => array_replace($english['cookie-policy'], [
                'title' => 'Πολιτική Cookies',
                'eyebrow' => 'Πληροφορίες απορρήτου',
                'intro' => 'Πώς χρησιμοποιούνται cookies για λειτουργία site, αναλυτικά, προτιμήσεις και διαφήμιση.',
            ]),
            'privacy-policy' => array_replace($english['privacy-policy'], [
                'title' => 'Πολιτική απορρήτου',
                'eyebrow' => 'Πληροφορίες απορρήτου',
                'intro' => 'Πώς επεξεργαζόμαστε προσωπικά δεδομένα για το e-shop και τις ψηφιακές υπηρεσίες.',
            ]),
            'terms-and-conditions' => array_replace($english['terms-and-conditions'], [
                'title' => 'Όροι και προϋποθέσεις',
                'eyebrow' => 'Νομικές πληροφορίες',
                'intro' => 'Όροι χρήσης του e-shop, παραγγελιών, πληρωμών, παράδοσης, επιστροφών και εγγυήσεων.',
            ]),
        ];
    }

    protected function storefrontContext(string $locale, string $page, array $params = []): array
    {
        return [
            'locale' => $locale,
            'htmlLang' => $locale === 'el' ? 'el' : 'en',
            'copy' => $this->copyMap($locale),
            'languageUrls' => $this->languageUrls($page, $params),
            'cartCount' => $this->cartCount(request()),
            'cartUrl' => $this->localizedPath($locale, 'cart', []),
            'cartAddUrl' => $this->localizedPath($locale, 'cart_add', []),
            'checkoutUrl' => $this->localizedPath($locale, 'checkout', []),
            'contactUrl' => $this->localizedPath($locale, 'contact', []),
            'shippingUrl' => $this->localizedPath($locale, 'shipping', []),
            'faqUrl' => $this->localizedPath($locale, 'faq', []),
            'cookiePolicyUrl' => $this->localizedPath($locale, 'cookie-policy', []),
            'privacyPolicyUrl' => $this->localizedPath($locale, 'privacy-policy', []),
            'termsUrl' => $this->localizedPath($locale, 'terms-and-conditions', []),
            'loginUrl' => $this->localizedPath($locale, 'login', []),
            'registerUrl' => $this->localizedPath($locale, 'register', []),
            'logoutUrl' => $this->localizedPath($locale, 'logout', []),
            'b2bRequestUrl' => $this->localizedPath($locale, 'b2b_request', []),
            'priceContext' => $this->priceContext(),
        ];
    }

    protected function languageUrls(string $page, array $params = []): array
    {
        $query = isset($params['q']) && $params['q'] !== '' ? ['q' => $params['q']] : [];

        return [
            'en' => $this->localizedPath('en', $page, $params, $query),
            'el' => $this->localizedPath('el', $page, $params, $query),
        ];
    }

    protected function localizedPath(string $locale, string $page, array $params, array $query = []): string
    {
        $prefix = '/' . $locale;

        $path = match ($page) {
            'category' => $prefix . '/category/' . ($params['slug'] ?? ''),
            'search' => $prefix . '/search',
            'outlet' => $prefix . '/outlet',
            'best_sellers' => $prefix . '/best-sellers',
            'product' => $prefix . '/product/' . ($params['slug'] ?? ''),
            'contact' => $prefix . '/contact',
            'shipping' => $prefix . '/shipping',
            'faq' => $prefix . '/faq',
            'cookie-policy' => $prefix . '/cookie-policy',
            'privacy-policy' => $prefix . '/privacy-policy',
            'terms-and-conditions' => $prefix . '/terms-and-conditions',
            'page' => $prefix . '/pages/' . ($params['slug'] ?? 'shipping'),
            'cart' => $prefix . '/cart',
            'checkout' => $prefix . '/checkout',
            'cart_add' => $prefix . '/cart/items',
            'cart_update' => $prefix . '/cart/items/' . ($params['product'] ?? ''),
            'cart_remove' => $prefix . '/cart/items/' . ($params['product'] ?? ''),
            'login' => $prefix . '/login',
            'register' => $prefix . '/register',
            'logout' => $prefix . '/logout',
            'b2b_request' => $prefix . '/b2b-request',
            default => $prefix,
        };

        if (isset($params['page']) && (int) $params['page'] > 1) {
            $query['page'] = (int) $params['page'];
        }

        return $query !== [] ? $path . '?' . http_build_query($query) : $path;
    }

    protected function copy(string $locale, string $key): string
    {
        return $this->copyMap($locale)[$key] ?? $this->copyMap('en')[$key] ?? $key;
    }

    protected function copyMap(string $locale): array
    {
        $copy = [
            'en' => [
                'account_label' => 'ID',
                'account' => 'Account',
                'add_to_cart' => 'Add to cart',
                'active_categories' => 'Active categories',
                'already_have_account' => 'Already have an account?',
                'already_registered' => 'Already registered?',
                'available' => 'Available',
                'best_sellers' => 'Best sellers',
                'best_sellers_hint' => 'Products ranked from completed storefront requests when available.',
                'brand' => 'Brand',
                'back' => 'Back',
                'brands' => 'Brands',
                'brands_indexed' => 'Brands indexed',
                'b2b_active' => 'B2B active',
                'b2b_already_active' => 'Your account already has B2B pricing.',
                'b2b_pending' => 'B2B pending',
                'b2b_request' => 'Request B2B',
                'b2b_request_hint' => 'Send your company details and an admin will switch the account to wholesale pricing.',
                'b2b_request_sent' => 'Your B2B request was sent. We will review it from the admin panel.',
                'b2b_request_title' => 'Request wholesale pricing',
                'b2b_status' => 'Pricing status',
                'browse_products' => 'Browse products',
                'business_catalog' => 'Business catalog',
                'business_copy' => 'Enterprise monitors, laptops, networking, components and infrastructure, presented as a modern buying surface powered by live database records.',
                'business_title' => 'Built for catalog scale, buyer speed and cleaner decisions.',
                'buyer_mode' => 'Buyer mode',
                'cart' => 'Cart',
                'cart_added' => 'Product added to cart.',
                'cart_empty' => 'Your cart is empty.',
                'cart_empty_hint' => 'Browse the catalog and add products for a quote or order.',
                'cart_removed' => 'Product removed from cart.',
                'cart_remove_confirm' => 'Are you sure you want to remove this item from your cart?',
                'cart_remove_title' => 'Remove item?',
                'cart_title' => 'Your cart',
                'cart_unavailable' => 'This product is not available for cart.',
                'cart_updated' => 'Cart updated.',
                'catalog_groups' => 'catalog groups',
                'catalog_products' => 'Catalog products',
                'catalog_view' => 'Catalog view',
                'cancel' => 'Cancel',
                'category' => 'Category',
                'category_empty' => 'No catalog records were found for this category.',
                'category_tools' => 'Catalog tools',
                'contact' => 'Contact',
                'contact_address' => 'Address',
                'contact_choose_store' => 'Preferred store',
                'contact_email_invalid' => 'Please enter a valid email address.',
                'contact_email_required' => 'Please enter your email address.',
                'contact_form_hint' => 'Send us a message and the right store team will get back to you.',
                'contact_intro' => 'Visit one of our stores, call us, or send a message directly from the storefront.',
                'contact_message' => 'Your message',
                'contact_message_required' => 'Please write a message with at least 10 characters.',
                'contact_name_required' => 'Please enter your full name.',
                'contact_sent' => 'Your message was sent. We will contact you soon.',
                'contact_store_main' => 'Main Store',
                'contact_store_city' => 'City Store',
                'contact_title' => 'Contact us',
                'change' => 'Change',
                'change_point' => 'Change point / Open map',
                'comments' => 'Comments',
                'continue_next_step' => 'Continue to next step',
                'continue_shopping' => 'Continue shopping',
                'checkout' => 'Checkout',
                'checkout_city_required' => 'Please enter the delivery city.',
                'checkout_delivery' => 'Delivery',
                'checkout_delivery_required' => 'Please enter the delivery address.',
                'checkout_empty' => 'Your cart is empty.',
                'checkout_failed' => 'Checkout request could not be sent. Please try again.',
                'checkout_intro' => 'Choose pickup or delivery and send the cart to our team.',
                'checkout_name_required' => 'Please enter your full name.',
                'checkout_pickup' => 'Store pickup',
                'checkout_pickup_required' => 'Please choose a pickup store.',
                'checkout_phone_required' => 'Please enter your phone number.',
                'checkout_sent' => 'Your request was sent',
                'checkout_submit' => 'Send order request',
                'checkout_tables_missing' => 'Checkout tables are not ready. Run the database migrations.',
                'delivery_address' => 'Delivery address',
                'delivery_method' => 'Delivery method',
                'database_powered' => 'Database powered',
                'deal' => 'Deal',
                'email' => 'Email',
                'fax' => 'Fax',
                'footer_cookie' => 'Cookie Policy',
                'footer_faq' => 'F.A.Q.',
                'footer_follow' => 'Follow us',
                'footer_info' => 'Info',
                'footer_intro' => 'We provide technology products, business equipment and support for home and business customers.',
                'footer_order' => 'Order',
                'footer_privacy' => 'Privacy Policy',
                'footer_shipping' => 'Shipping',
                'footer_social' => 'Find us social',
                'footer_subscribe' => 'Subscribe',
                'footer_terms' => 'Terms and Conditions',
                'city' => 'City',
                'company_address' => 'Company address',
                'company_name' => 'Company name',
                'fast_cards' => 'Fast cards',
                'forgot_password' => 'Forgot password?',
                'fast_scan' => 'Fast scan',
                'featured_hint' => 'Recommended products from the live catalog.',
                'find_similar' => 'Find similar',
                'gallery_images' => 'Gallery images',
                'guided_path' => 'Guided path',
                'guided_sections' => 'guided sections available for this workspace.',
                'home' => 'Home',
                'hot_deals' => 'Hot Deals',
                'image_pending' => 'Image pending',
                'imported_images' => 'Imported images',
                'language' => 'Language',
                'live_catalog' => 'Live catalog',
                'live_database_storefront' => 'Live database storefront',
                'live_db' => 'Live DB',
                'live_products' => 'Live products',
                'live_records' => 'Live records',
                'login_hint' => 'Sign in to keep cart sessions, request quotes, and prepare account pricing workflows for your company.',
                'login_title' => 'Access your account',
                'logout' => 'Logout',
                'name' => 'Name',
                'new_in' => 'New In',
                'new_in_hint' => 'Fresh product records from the catalog.',
                'no_account' => 'No account yet?',
                'no_products' => 'No products found.',
                'on_request' => 'On request',
                'order_summary' => 'Order summary',
                'open_workspace' => 'Open workspace',
                'outlet' => 'Outlet',
                'outlet_hint' => 'Deals, clearance and outlet catalog records.',
                'occupation' => 'Occupation',
                'password' => 'Password',
                'phone' => 'Phone',
                'postcode' => 'Postcode',
                'confirm_password' => 'Confirm password',
                'product' => 'Product',
                'product_gallery' => 'Product gallery',
                'product_gallery_hint' => 'Live catalog products with imported images.',
                'products' => 'products',
                'products_available' => 'products available in this catalog group.',
                'quantity' => 'Quantity',
                'quick_brand_searches' => 'Quick brand searches',
                'responsive' => 'Responsive',
                'retail' => 'Retail',
                'register' => 'Register',
                'register_hint' => 'Register once, then build carts and buying lists around the live catalog.',
                'register_title' => 'Create your account',
                'remove' => 'Remove',
                'remember_me' => 'Remember me',
                'search' => 'Search',
                'search_empty_hint' => 'Type a product, SKU, brand or code.',
                'search_placeholder' => 'Search products, SKU, brand...',
                'search_products' => 'Search products',
                'search_results_hint' => 'Results from product title, SKU, MPN and EAN.',
                'search_view' => 'Search view',
                'sections' => 'Sections',
                'shipping' => 'Shipping',
                'shipping_cost' => 'Shipping cost',
                'ship_delivery_akis' => 'Delivery with Akis Express',
                'ship_pickup_akis' => 'Pickup from an Akis express point',
                'ship_boxnow' => 'BOX NOW Lockers | 24/7',
                'ship_pickup_main' => 'Pickup from Main Store',
                'payment' => 'Payment',
                'review' => 'Review',
                'send_quote_request' => 'Send quote request',
                'send_request' => 'Send request',
                'notes' => 'Notes',
                'mobile' => 'Mobile',
                'service_quote' => 'Business quote',
                'service_quote_hint' => 'Send a SKU list and prepare a company order flow.',
                'service_repair' => 'Repair desk',
                'service_repair_hint' => 'Route devices, parts and warranty requests from the storefront.',
                'service_request' => 'Parts request',
                'service_request_hint' => 'Ask for a product that is not listed and keep the request attached to the catalog.',
                'service_trade' => 'Trade-in path',
                'service_trade_hint' => 'Prepare future upgrade journeys for laptops, desktops and displays.',
                'services_hint' => 'Helpful service entry points inspired by modern electronics shops, connected to the catalog experience.',
                'services_title' => 'More than product tiles',
                'sections_available' => 'sections available',
                'sign_in' => 'Sign in',
                'sign_in_hint' => 'Sign in to get account pricing and saved buying lists',
                'sku_on_request' => 'Code on request',
                'start_from_job' => 'Start from the job to be done',
                'subcategory' => 'Subcategory',
                'subtotal' => 'Subtotal',
                'tax_note_b2b' => 'B2B wholesale price, without VAT.',
                'tax_note_retail' => 'Retail price with VAT where available.',
                'technical_specs' => 'Technical specs',
                'update_cart' => 'Update cart',
                'vat_number' => 'VAT number',
                'view' => 'View',
                'with_images_first' => 'With images first',
                'workspace_hint' => 'Modern shortcuts built from the same category logic.',
                'workspaces' => 'Buying workspaces',
            ],
            'el' => [
                'account_label' => 'ID',
                'account' => 'Λογαριασμός',
                'add_to_cart' => 'Προσθήκη',
                'active_categories' => 'Ενεργές κατηγορίες',
                'already_have_account' => 'Έχετε ήδη λογαριασμό;',
                'already_registered' => 'Έχετε ήδη εγγραφεί;',
                'available' => 'Διαθέσιμο',
                'best_sellers' => 'Best sellers',
                'best_sellers_hint' => 'Προϊόντα ταξινομημένα από πραγματικά αιτήματα όταν υπάρχουν.',
                'brand' => 'Μάρκα',
                'back' => 'Πίσω',
                'brands' => 'Μάρκες',
                'brands_indexed' => 'Μάρκες',
                'b2b_active' => 'Ενεργό B2B',
                'b2b_already_active' => 'Ο λογαριασμός έχει ήδη τιμές B2B.',
                'b2b_pending' => 'Εκκρεμεί B2B',
                'b2b_request' => 'Αίτηση B2B',
                'b2b_request_hint' => 'Στείλτε τα στοιχεία εταιρείας και ο διαχειριστής θα αλλάξει τον λογαριασμό σε χονδρική.',
                'b2b_request_sent' => 'Η αίτηση B2B στάλθηκε. Θα ελεγχθεί από το admin panel.',
                'b2b_request_title' => 'Αίτηση χονδρικής τιμής',
                'b2b_status' => 'Κατάσταση τιμολόγησης',
                'browse_products' => 'Προβολή προϊόντων',
                'business_catalog' => 'Επαγγελματικός κατάλογος',
                'business_copy' => 'Οθόνες, laptops, networking, components και υποδομές μέσα από σύγχρονη εμπειρία αγοράς με πραγματικά δεδομένα βάσης.',
                'business_title' => 'Σχεδιασμένο για μεγάλο κατάλογο, γρήγορη επιλογή και καθαρές αποφάσεις.',
                'buyer_mode' => 'Buyer mode',
                'cart' => 'Καλάθι',
                'cart_added' => 'Το προϊόν προστέθηκε στο καλάθι.',
                'cart_empty' => 'Το καλάθι είναι άδειο.',
                'cart_empty_hint' => 'Περιηγηθείτε στον κατάλογο και προσθέστε προϊόντα για προσφορά ή παραγγελία.',
                'cart_removed' => 'Το προϊόν αφαιρέθηκε από το καλάθι.',
                'cart_remove_confirm' => 'Είστε σίγουροι ότι θέλετε να αφαιρέσετε αυτό το προϊόν από το καλάθι σας;',
                'cart_remove_title' => 'Αφαίρεση προϊόντος;',
                'cart_title' => 'Το καλάθι σας',
                'cart_unavailable' => 'Το προϊόν δεν είναι διαθέσιμο για καλάθι.',
                'cart_updated' => 'Το καλάθι ενημερώθηκε.',
                'catalog_groups' => 'ομάδες καταλόγου',
                'catalog_products' => 'Προϊόντα καταλόγου',
                'catalog_view' => 'Προβολή καταλόγου',
                'cancel' => 'Άκυρο',
                'category' => 'Κατηγορία',
                'category_empty' => 'Δεν βρέθηκαν προϊόντα για αυτή την κατηγορία.',
                'category_tools' => 'Εργαλεία καταλόγου',
                'contact' => 'Επικοινωνία',
                'contact_address' => 'Διεύθυνση',
                'contact_choose_store' => 'Κατάστημα προτίμησης',
                'contact_email_invalid' => 'Πληκτρολογήστε έγκυρο email.',
                'contact_email_required' => 'Πληκτρολογήστε το email σας.',
                'contact_form_hint' => 'Στείλτε μας μήνυμα και η κατάλληλη ομάδα θα επικοινωνήσει μαζί σας.',
                'contact_intro' => 'Επισκεφθείτε ένα από τα καταστήματά μας, καλέστε μας ή στείλτε μήνυμα απευθείας από το storefront.',
                'contact_message' => 'Το μήνυμά σας',
                'contact_message_required' => 'Γράψτε μήνυμα με τουλάχιστον 10 χαρακτήρες.',
                'contact_name_required' => 'Πληκτρολογήστε το ονοματεπώνυμό σας.',
                'contact_sent' => 'Το μήνυμα στάλθηκε. Θα επικοινωνήσουμε μαζί σας σύντομα.',
                'contact_store_main' => 'Κεντρικό κατάστημα',
                'contact_store_city' => 'Κατάστημα πόλης',
                'contact_title' => 'Επικοινωνία',
                'change' => 'Αλλαγή',
                'change_point' => 'Αλλαγή σημείου / Χάρτης',
                'comments' => 'Σχόλια',
                'continue_next_step' => 'Συνέχεια στο επόμενο βήμα',
                'continue_shopping' => 'Συνέχεια αγορών',
                'checkout' => 'Checkout',
                'checkout_city_required' => 'Πληκτρολογήστε την πόλη παράδοσης.',
                'checkout_delivery' => 'Παράδοση',
                'checkout_delivery_required' => 'Πληκτρολογήστε τη διεύθυνση παράδοσης.',
                'checkout_empty' => 'Το καλάθι είναι άδειο.',
                'checkout_failed' => 'Το αίτημα δεν στάλθηκε. Δοκιμάστε ξανά.',
                'checkout_intro' => 'Επιλέξτε παραλαβή ή παράδοση και στείλτε το καλάθι στην ομάδα μας.',
                'checkout_name_required' => 'Πληκτρολογήστε το ονοματεπώνυμό σας.',
                'checkout_pickup' => 'Παραλαβή από κατάστημα',
                'checkout_pickup_required' => 'Επιλέξτε κατάστημα παραλαβής.',
                'checkout_phone_required' => 'Πληκτρολογήστε το τηλέφωνό σας.',
                'checkout_sent' => 'Το αίτημα στάλθηκε',
                'checkout_submit' => 'Αποστολή αιτήματος παραγγελίας',
                'checkout_tables_missing' => 'Οι πίνακες checkout δεν είναι έτοιμοι. Τρέξτε τα migrations.',
                'delivery_address' => 'Διεύθυνση παράδοσης',
                'delivery_method' => 'Τρόπος παραλαβής',
                'database_powered' => 'Από τη βάση',
                'deal' => 'Προσφορά',
                'email' => 'Email',
                'fax' => 'Fax',
                'footer_cookie' => 'Πολιτική Cookies',
                'footer_faq' => 'Συχνές ερωτήσεις',
                'footer_follow' => 'Ακολουθήστε μας',
                'footer_info' => 'Πληροφορίες',
                'footer_intro' => 'Παρέχουμε προϊόντα τεχνολογίας, επαγγελματικό εξοπλισμό και υποστήριξη για ιδιώτες και επιχειρήσεις.',
                'footer_order' => 'Παραγγελία',
                'footer_privacy' => 'Πολιτική απορρήτου',
                'footer_shipping' => 'Αποστολή',
                'footer_social' => 'Βρείτε μας στα social',
                'footer_subscribe' => 'Εγγραφή',
                'footer_terms' => 'Όροι και προϋποθέσεις',
                'city' => 'Πόλη',
                'company_address' => 'Διεύθυνση εταιρείας',
                'company_name' => 'Επωνυμία εταιρείας',
                'fast_cards' => 'Γρήγορες κάρτες',
                'forgot_password' => 'Ξεχάσατε τον κωδικό;',
                'fast_scan' => 'Γρήγορη ανάγνωση',
                'featured_hint' => 'Προτεινόμενα προϊόντα από τον ζωντανό κατάλογο.',
                'find_similar' => 'Παρόμοια',
                'gallery_images' => 'Εικόνες gallery',
                'guided_path' => 'Καθοδηγούμενη διαδρομή',
                'guided_sections' => 'ενότητες διαθέσιμες για αυτή την περιοχή.',
                'home' => 'Αρχική',
                'hot_deals' => 'Προσφορές',
                'image_pending' => 'Εκκρεμεί εικόνα',
                'imported_images' => 'Εισαγμένες εικόνες',
                'language' => 'Γλώσσα',
                'live_catalog' => 'Ζωντανός κατάλογος',
                'live_database_storefront' => 'Storefront από τη βάση',
                'live_db' => 'Live DB',
                'live_products' => 'Προϊόντα',
                'live_records' => 'Ζωντανές εγγραφές',
                'login_hint' => 'Συνδεθείτε για καλάθια, αιτήματα προσφοράς και ροές τιμολόγησης λογαριασμού.',
                'login_title' => 'Σύνδεση στον λογαριασμό σας',
                'logout' => 'Αποσύνδεση',
                'name' => 'Όνομα',
                'new_in' => 'Νέες αφίξεις',
                'new_in_hint' => 'Νέες εγγραφές προϊόντων από τον κατάλογο.',
                'no_account' => 'Δεν έχετε λογαριασμό;',
                'no_products' => 'Δεν βρέθηκαν προϊόντα.',
                'on_request' => 'Κατόπιν ζήτησης',
                'order_summary' => 'Σύνοψη παραγγελίας',
                'open_workspace' => 'Άνοιγμα',
                'outlet' => 'Outlet',
                'outlet_hint' => 'Προσφορές, clearance και outlet προϊόντα καταλόγου.',
                'occupation' => 'Επάγγελμα / δραστηριότητα',
                'password' => 'Κωδικός',
                'phone' => 'Τηλέφωνο',
                'postcode' => 'Τ.Κ.',
                'confirm_password' => 'Επιβεβαίωση κωδικού',
                'product' => 'Προϊόν',
                'product_gallery' => 'Gallery προϊόντων',
                'product_gallery_hint' => 'Προϊόντα καταλόγου με εισαγμένες εικόνες.',
                'products' => 'προϊόντα',
                'products_available' => 'προϊόντα διαθέσιμα σε αυτή την ομάδα.',
                'quantity' => 'Ποσότητα',
                'quick_brand_searches' => 'Γρήγορη αναζήτηση μάρκας',
                'responsive' => 'Responsive',
                'retail' => 'Λιανική',
                'register' => 'Εγγραφή',
                'register_hint' => 'Δημιουργήστε λογαριασμό για καλάθια και λίστες αγοράς στον ζωντανό κατάλογο.',
                'register_title' => 'Δημιουργία λογαριασμού',
                'remove' => 'Αφαίρεση',
                'remember_me' => 'Να με θυμάσαι',
                'search' => 'Αναζήτηση',
                'search_empty_hint' => 'Πληκτρολογήστε προϊόν, SKU, μάρκα ή κωδικό.',
                'search_placeholder' => 'Αναζήτηση προϊόντων, SKU, μάρκας...',
                'search_products' => 'Αναζήτηση προϊόντων',
                'search_results_hint' => 'Αποτελέσματα από τίτλο, SKU, MPN και EAN.',
                'search_view' => 'Προβολή αναζήτησης',
                'sections' => 'Ενότητες',
                'shipping' => 'Αποστολή',
                'shipping_cost' => 'Κόστος αποστολής',
                'ship_delivery_akis' => 'Παράδοση με Akis Express',
                'ship_pickup_akis' => 'Παραλαβή από σημείο Akis Express',
                'ship_boxnow' => 'BOX NOW Lockers | 24/7',
                'ship_pickup_main' => 'Παραλαβή από το κεντρικό κατάστημα',
                'payment' => 'Πληρωμή',
                'review' => 'Έλεγχος',
                'send_quote_request' => 'Αποστολή αιτήματος προσφοράς',
                'send_request' => 'Αποστολή αίτησης',
                'notes' => 'Σχόλια',
                'mobile' => 'Κινητό',
                'service_quote' => 'Προσφορά B2B',
                'service_quote_hint' => 'Αποστολή λίστας SKU και προετοιμασία εταιρικής παραγγελίας.',
                'service_repair' => 'Repair desk',
                'service_repair_hint' => 'Διαχείριση συσκευών, ανταλλακτικών και αιτημάτων εγγύησης.',
                'service_request' => 'Αίτημα προϊόντος',
                'service_request_hint' => 'Ζητήστε προϊόν που δεν υπάρχει ακόμη στον κατάλογο.',
                'service_trade' => 'Trade-in',
                'service_trade_hint' => 'Μελλοντική ροή αναβάθμισης για laptops, desktops και οθόνες.',
                'services_hint' => 'Χρήσιμες υπηρεσίες όπως σε σύγχρονα electronics shops, δεμένες με τον κατάλογο.',
                'services_title' => 'Πέρα από απλές κάρτες προϊόντων',
                'sections_available' => 'ενότητες διαθέσιμες',
                'sign_in' => 'Σύνδεση',
                'sign_in_hint' => 'Συνδεθείτε για τιμές λογαριασμού και λίστες αγοράς',
                'sku_on_request' => 'Κωδικός κατόπιν ζήτησης',
                'start_from_job' => 'Ξεκινήστε από την ανάγκη',
                'subcategory' => 'Υποκατηγορία',
                'subtotal' => 'Υποσύνολο',
                'tax_note_b2b' => 'Χονδρική τιμή B2B, χωρίς ΦΠΑ.',
                'tax_note_retail' => 'Λιανική τιμή με ΦΠΑ όπου υπάρχει.',
                'technical_specs' => 'Τεχνικά χαρακτηριστικά',
                'update_cart' => 'Ενημέρωση',
                'vat_number' => 'Αριθμός ΦΠΑ',
                'view' => 'Προβολή',
                'with_images_first' => 'Πρώτα με εικόνες',
                'workspace_hint' => 'Σύγχρονες συντομεύσεις από την ίδια λογική κατηγοριών.',
                'workspaces' => 'Buying workspaces',
            ],
        ];

        return $copy[$locale] ?? $copy['en'];
    }

    protected function contactStores(): array
    {
        return [
            [
                'key' => 'main',
                'name_key' => 'contact_store_main',
                'address' => '1 Main Street, 1000 City',
                'phone' => '+1 000 000 0000',
                'fax' => '+1 000 000 0001',
                'email' => 'sales@example.com',
                'map' => 'https://www.google.com/maps?q=Main%20Street&output=embed',
                'map_link' => null,
            ],
            [
                'key' => 'city',
                'name_key' => 'contact_store_city',
                'address' => '25 Market Avenue, 2000 City',
                'phone' => '+1 000 000 0002',
                'fax' => '+1 000 000 0003',
                'email' => 'sales@example.com',
                'map' => 'https://www.google.com/maps?q=Market%20Avenue&output=embed',
                'map_link' => null,
            ],
        ];
    }

    protected function nextOrderNumber(): string
    {
        do {
            $number = 'CL-' . now()->format('ymd') . '-' . random_int(1000, 9999);
        } while ($this->tableReady('storefront_orders') && DB::table('storefront_orders')->where('order_number', $number)->exists());

        return $number;
    }

    protected function mailCheckoutRequest(string $orderNumber, array $data, array $items, float $subtotal, array $priceContext): void
    {
        $recipient = (string) config('mail.contact_to', 'sales@example.com');
        $lines = [
            'New storefront order request: #' . $orderNumber,
            '',
            'Customer: ' . $data['customer_name'],
            'Email: ' . $data['customer_email'],
            'Phone: ' . $data['customer_phone'],
            'Company: ' . ($data['company_name'] ?? '-'),
            'VAT: ' . ($data['vat_number'] ?? '-'),
            'Pricing: ' . ($priceContext['group'] ?? 'retail') . ' - ' . ($priceContext['note'] ?? ''),
            '',
            'Fulfillment: ' . $data['fulfillment_method'],
            'Pickup store: ' . ($data['pickup_store'] ?? '-'),
            'Delivery address: ' . ($data['delivery_address'] ?? '-'),
            'City/Postcode: ' . trim(($data['city'] ?? '-') . ' ' . ($data['postcode'] ?? '')),
            '',
            'Items:',
        ];

        foreach ($items as $item) {
            $lines[] = sprintf(
                '- %sx %s | Code %s | %s',
                (int) ($item['quantity'] ?? 1),
                (string) ($item['title'] ?? 'Product'),
                (string) ($item['display_code'] ?? $item['code'] ?? '-'),
                (string) ($item['line_total'] ?? '')
            );
        }

        $lines[] = '';
        $lines[] = 'Subtotal: ' . $this->money($subtotal);
        $lines[] = '';
        $lines[] = 'Notes:';
        $lines[] = (string) ($data['notes'] ?? '-');

        Mail::raw(implode("\n", $lines), function ($message) use ($data, $recipient, $orderNumber): void {
            $message
                ->to($recipient)
                ->replyTo($data['customer_email'], $data['customer_name'])
                ->subject('Order request #' . $orderNumber);
        });
    }

    protected function banners(): array
    {
        if (! $this->tableReady('home_banners')) {
            return [];
        }

        try {
            $query = DB::table('home_banners')
                ->where('is_active', true)
                ->where(function ($query): void {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function ($query): void {
                    $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
                });

            if (Schema::hasColumn('home_banners', 'placement')) {
                $query->where(function ($query): void {
                    $query->where('placement', 'top')->orWhereNull('placement');
                });
            }

            $banners = $query
                ->orderBy('sort_order')
                ->orderByDesc('id')
                ->limit(8)
                ->get()
                ->map(fn ($banner): array => [
                    'title' => (string) ($banner->title ?? ''),
                    'subtitle' => (string) ($banner->subtitle ?? ''),
                    'media_type' => (string) ($banner->media_type ?? 'image'),
                    'media' => $this->bannerMediaUrl($banner->media_path ?? null, $banner->media_url ?? null),
                    'product_refs' => (string) ($banner->product_refs ?? ''),
                    'products' => $this->bannerProducts((string) ($banner->product_refs ?? '')),
                    'link_url' => (string) ($banner->link_url ?? '#'),
                    'button_label' => (string) ($banner->button_label ?? ''),
                    'opens_new_tab' => (bool) ($banner->opens_new_tab ?? false),
                ])
                ->filter(fn (array $banner): bool => $this->bannerHasContent($banner))
                ->values()
                ->all();

            return $banners;
        } catch (\Throwable) {
            return [];
        }
    }

    protected function bannerProducts(string $refs, int $limit = 3): array
    {
        $refs = $this->parseProductRefs($refs);

        if ($refs === [] || ! $this->tableReady('products')) {
            return [];
        }

        try {
            $products = $this->productsQuery()
                ->where(function ($query) use ($refs): void {
                    foreach (['code', 'ean', 'mpn', 'slug'] as $column) {
                        if (Schema::hasColumn('products', $column)) {
                            $query->orWhereIn('products.' . $column, $refs);
                        }
                    }

                    $ids = array_values(array_filter($refs, fn (string $ref): bool => ctype_digit($ref)));

                    if ($ids !== []) {
                        $query->orWhereIn('products.id', array_map('intval', $ids));
                    }
                })
                ->limit(40)
                ->get()
                ->map(fn ($product): array => $this->mapProduct($product, 'Hero'))
                ->all();

            $ranked = collect($products)
                ->sortBy(fn (array $product): int => $this->productRefRank($product, $refs))
                ->groupBy(fn (array $product): string => $this->bannerProductDuplicateKey($product))
                ->map(fn ($group) => $this->bestBannerProduct($group->values()->all(), $refs))
                ->filter()
                ->sortBy(fn (array $product): int => $this->productRefRank($product, $refs))
                ->take($limit)
                ->values()
                ->all();

            return $ranked;
        } catch (\Throwable) {
            return [];
        }
    }

    protected function bannerHasContent(array $banner): bool
    {
        return trim((string) ($banner['title'] ?? '')) !== ''
            || trim((string) ($banner['subtitle'] ?? '')) !== ''
            || trim((string) ($banner['media'] ?? '')) !== ''
            || ($banner['products'] ?? []) !== [];
    }

    protected function parseProductRefs(string $refs): array
    {
        return collect(preg_split('/[\r\n,;|]+/', $refs) ?: [])
            ->map(fn (string $ref): string => trim($ref))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function productRefRank(array $product, array $refs): int
    {
        foreach ($refs as $index => $ref) {
            foreach (['id', 'code', 'ean', 'mpn'] as $field) {
                if ((string) ($product[$field] ?? '') === $ref) {
                    return $index;
                }
            }

            $slug = basename((string) ($product['url'] ?? ''));

            if ($slug === $ref) {
                return $index;
            }
        }

        return count($refs) + 1;
    }

    protected function bannerProductDuplicateKey(array $product): string
    {
        $mpn = trim(mb_strtolower((string) ($product['mpn'] ?? '')));

        return $mpn !== '' ? 'mpn:' . $mpn : 'id:' . (string) ($product['id'] ?? '');
    }

    protected function bestBannerProduct(array $products, array $refs): ?array
    {
        return collect($products)
            ->sortBy([
                fn (array $product): int => trim((string) ($product['image'] ?? '')) !== '' ? 0 : 1,
                fn (array $product): int => $this->productRefRank($product, $refs),
                fn (array $product): int => -1 * (int) ($product['id'] ?? 0),
            ])
            ->first();
    }

    protected function categories(): array
    {
        if (! $this->tableReady('categories')) {
            return [];
        }

        try {
            $query = DB::table('categories');

            if (Schema::hasColumn('categories', 'active')) {
                $query->where('active', true);
            }

            $categories = $query
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn ($category): array => [
                    'id' => (int) ($category->id ?? 0),
                    'parent_id' => isset($category->parent_id) ? (int) $category->parent_id : null,
                    'name' => (string) ($category->name ?? 'Category'),
                    'slug' => (string) ($category->slug ?? '#'),
                    'full_path' => (string) ($category->full_path ?? $category->name ?? ''),
                    'url_path' => $this->categoryUrlPath($category),
                    'image' => $this->mediaUrl($category->category_image ?? null, null),
                    'count' => null,
                    'children' => [],
                ])
                ->all();

            if ($categories === []) {
                return [];
            }

            return $this->buildCategoryTree($categories);
        } catch (\Throwable) {
            return [];
        }
    }

    protected function offers(): array
    {
        if (! $this->tableReady('shop_offers')) {
            return [];
        }

        try {
            return DB::table('shop_offers')
                ->leftJoin('products', 'products.id', '=', 'shop_offers.product_id')
                ->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id')
                ->leftJoin('suppliers as offer_suppliers', 'offer_suppliers.id', '=', 'products.primary_supplier_id')
                ->select(
                    'shop_offers.*',
                    'products.id as offer_product_id',
                    'products.code as product_code',
                    'products.primary_supplier_id as offer_primary_supplier_id',
                    $this->supplierStorefrontCodeSelect('offer_suppliers', 'offer_primary_supplier_id_storefront_code'),
                    'products.main_image as product_image',
                    'products.image_path as product_image_path',
                    'products.vat_rate as product_vat_rate',
                    'product_prices.retail_inc_vat as product_retail_inc_vat',
                )
                ->where('shop_offers.is_active', true)
                ->where(function ($query): void {
                    $query->whereNull('shop_offers.starts_at')->orWhere('shop_offers.starts_at', '<=', now());
                })
                ->where(function ($query): void {
                    $query->whereNull('shop_offers.ends_at')->orWhere('shop_offers.ends_at', '>=', now());
                })
                ->orderBy('shop_offers.sort_order')
                ->orderByDesc('shop_offers.id')
                ->limit(8)
                ->get()
                ->map(function ($offer): array {
                    $prices = $this->offerPricesForCurrentCustomer(
                        $offer->offer_price ?? null,
                        $offer->regular_price ?? $offer->product_retail_inc_vat ?? null,
                        $offer->product_vat_rate ?? null,
                    );

                    return [
                        'title' => (string) ($offer->title ?? 'Offer'),
                        'badge' => (string) ($offer->badge ?? 'Offer'),
                        'code' => (string) ($offer->product_code ?? ''),
                        'display_code' => $this->productDisplayCode($offer, 'offer_product_id', 'offer_primary_supplier_id', 'product_code'),
                        'image' => $this->mediaUrl($offer->image_path ?? $offer->product_image_path ?? null, $offer->product_image ?? null),
                        'link_url' => (string) ($offer->link_url ?? '#'),
                        'regular_price' => $this->money($prices['regular']),
                        'offer_price' => $this->money($prices['offer']),
                        'discount_percent' => $this->percent($offer->discount_percent ?? null),
                    ];
                })
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function featuredSection(): array
    {
        if (! $this->tableReady('home_product_sections')) {
            return ['title' => 'Featured picks', 'type' => 'recommended', 'is_active' => true];
        }

        try {
            $section = DB::table('home_product_sections')
                ->where('section_key', 'home_featured_products')
                ->first();

            return [
                'title' => (string) ($section->title ?? 'Featured picks'),
                'type' => (string) ($section->item_type ?? 'recommended'),
                'is_active' => (bool) ($section->is_active ?? true),
            ];
        } catch (\Throwable) {
            return ['title' => 'Featured picks', 'type' => 'recommended', 'is_active' => true];
        }
    }

    protected function featuredProducts(): array
    {
        if (! $this->tableReady('featured_products')) {
            return $this->imageProducts(8);
        }

        try {
            $query = DB::table('featured_products')
                ->leftJoin('products', 'products.id', '=', 'featured_products.product_id')
                ->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id')
                ->when($this->activeOffersReady(), function ($query) {
                    $query->leftJoinSub($this->activeOffersSubquery(), 'active_offers', function ($join): void {
                        $join->on('active_offers.product_id', '=', 'products.id');
                    });
                });

            if (Schema::hasTable('suppliers') && Schema::hasColumn('products', 'primary_supplier_id')) {
                $query->leftJoin('suppliers as featured_suppliers', 'featured_suppliers.id', '=', 'products.primary_supplier_id');
            }

            $select = [
                'featured_products.id as featured_product_id',
                'featured_products.label',
                'products.id',
                'products.title',
                'products.code',
                'products.slug',
                'products.main_image',
                'products.image_path',
                'products.vat_rate',
                'products.primary_supplier_id',
                'product_prices.retail_inc_vat',
                'product_prices.discount_inc_vat',
                'product_prices.wholesale_inc_vat',
            ];

            if ($this->activeOffersReady()) {
                $select[] = DB::raw('active_offers.offer_price as active_offer_price');
                $select[] = DB::raw('active_offers.regular_price as active_offer_regular_price');
                $select[] = DB::raw('active_offers.discount_percent as active_offer_discount_percent');
                $select[] = DB::raw('active_offers.badge as active_offer_badge');
            }

            if (Schema::hasTable('suppliers') && Schema::hasColumn('products', 'primary_supplier_id')) {
                $select[] = $this->supplierStorefrontCodeSelect('featured_suppliers', 'primary_supplier_storefront_code');
            } else {
                $select[] = DB::raw('NULL as primary_supplier_storefront_code');
            }

            if (Schema::hasTable('supplier_products') && Schema::hasColumn('supplier_products', 'matched_product_id')) {
                $select[] = DB::raw('(select supplier_products.supplier_id from supplier_products where supplier_products.matched_product_id = products.id order by supplier_products.last_seen_at desc, supplier_products.id desc limit 1) as supplier_product_supplier_id');
                $select[] = DB::raw('(select ' . $this->supplierStorefrontCodeExpression('latest_suppliers') . ' from supplier_products join suppliers as latest_suppliers on latest_suppliers.id = supplier_products.supplier_id where supplier_products.matched_product_id = products.id order by supplier_products.last_seen_at desc, supplier_products.id desc limit 1) as supplier_product_supplier_storefront_code');
            } else {
                $select[] = DB::raw('NULL as supplier_product_supplier_id');
                $select[] = DB::raw('NULL as supplier_product_supplier_storefront_code');
            }

            $items = $query
                ->select($select)
                ->where('featured_products.is_active', true)
                ->where(function ($query): void {
                    $query->whereNull('featured_products.starts_at')->orWhere('featured_products.starts_at', '<=', now());
                })
                ->where(function ($query): void {
                    $query->whereNull('featured_products.ends_at')->orWhere('featured_products.ends_at', '>=', now());
                })
                ->orderBy('featured_products.sort_order')
                ->orderByDesc('featured_products.id')
                ->limit(8)
                ->get()
                ->map(fn ($product): array => $this->mapProduct($product, $product->label ?? null))
                ->all();

            return $items !== [] ? $items : $this->imageProducts(8);
        } catch (\Throwable) {
            return $this->imageProducts(8);
        }
    }

    protected function latestProducts(): array
    {
        if (! $this->tableReady('products')) {
            return [];
        }

        try {
            $query = $this->productsQuery()->limit(8);

            if (Schema::hasColumn('products', 'status')) {
                $query->where('products.status', 'active');
            }

            $products = $this->orderByImagesFirst($query)
                ->orderByDesc('products.id')
                ->get()
                ->map(fn ($product): array => $this->mapProduct($product, 'New'))
                ->all();

            return $products;
        } catch (\Throwable) {
            return [];
        }
    }

    protected function imageProducts(int $limit = 10): array
    {
        if (! $this->tableReady('products')) {
            return [];
        }

        try {
            return $this->productsQuery()
                ->where(function ($query): void {
                    if (Schema::hasColumn('products', 'image_path')) {
                        $query->whereNotNull('products.image_path')->where('products.image_path', '<>', '');
                    }

                    if (Schema::hasColumn('products', 'main_image')) {
                        $query->orWhere(function ($builder): void {
                            $builder->whereNotNull('products.main_image')->where('products.main_image', '<>', '');
                        });
                    }
                })
                ->orderByDesc('products.id')
                ->limit($limit)
                ->get()
                ->map(fn ($product): array => $this->mapProduct($product, 'Gallery'))
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function topBrands(int $limit = 16): array
    {
        if (! $this->tableReady('brands') || ! $this->tableReady('products')) {
            return [];
        }

        try {
            return DB::table('brands')
                ->join('products', 'products.brand_id', '=', 'brands.id')
                ->select('brands.name', 'brands.slug', DB::raw('count(products.id) as products_count'))
                ->whereNotNull('brands.name')
                ->when(Schema::hasColumn('brands', 'active'), fn ($query) => $query->where('brands.active', true))
                ->when(Schema::hasColumn('products', 'status'), fn ($query) => $query->whereIn('products.status', ['active', 'enabled', 'yes', '1', 'true']))
                ->when(Schema::hasColumn('products', 'deleted_at'), fn ($query) => $query->whereNull('products.deleted_at'))
                ->groupBy('brands.id', 'brands.name', 'brands.slug')
                ->orderByDesc('products_count')
                ->orderBy('brands.name')
                ->limit($limit)
                ->get()
                ->map(fn ($brand): array => [
                    'name' => (string) $brand->name,
                    'slug' => (string) ($brand->slug ?? ''),
                    'count' => (int) $brand->products_count,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function catalogStats(): array
    {
        $stats = [
            'products' => 0,
            'categories' => 0,
            'brands' => 0,
            'images' => 0,
        ];

        try {
            if ($this->tableReady('products')) {
                $query = DB::table('products');

                if (Schema::hasColumn('products', 'status')) {
                    $query->whereIn('status', ['active', 'enabled', 'yes', '1', 'true']);
                }

                if (Schema::hasColumn('products', 'deleted_at')) {
                    $query->whereNull('deleted_at');
                }

                $stats['products'] = (int) $query->count();
            }

            if ($this->tableReady('categories')) {
                $query = DB::table('categories');

                if (Schema::hasColumn('categories', 'active')) {
                    $query->where('active', true);
                }

                $stats['categories'] = (int) $query->count();
            }

            if ($this->tableReady('brands')) {
                $query = DB::table('brands');

                if (Schema::hasColumn('brands', 'active')) {
                    $query->where('active', true);
                }

                $stats['brands'] = (int) $query->count();
            }

            if ($this->tableReady('product_images')) {
                $stats['images'] = (int) DB::table('product_images')->count();
            }
        } catch (\Throwable) {
            return $stats;
        }

        return $stats;
    }

    protected function emptyProduct(string $slug): array
    {
        return [
            'title' => 'Product not found',
            'code' => strtoupper($slug),
            'label' => 'Unavailable',
            'image' => '',
            'images' => [],
            'price' => 'Call',
            'old_price' => '',
            'url' => $this->productUrl($slug),
            'description' => 'This product URL does not match a catalog record.',
            'category' => '',
            'brand' => '',
            'mpn' => '',
            'ean' => '',
        ];
    }

    protected function mapProduct(object $product, ?string $label = null, bool $includeGallery = false): array
    {
        $price = $this->productPriceForCurrentCustomer($product);
        $image = $this->mediaUrl($product->image_path ?? $product->main_image ?? null, null);
        $images = $includeGallery ? $this->productImages((int) ($product->id ?? 0), $image) : array_values(array_filter([$image]));

        return [
            'id' => (int) ($product->id ?? 0),
            'title' => (string) ($product->title ?? 'Product'),
            'code' => (string) ($product->code ?? ''),
            'display_code' => $this->productDisplayCode($product),
            'mpn' => (string) ($product->mpn ?? ''),
            'ean' => (string) ($product->ean ?? ''),
            'label' => (string) ($label ?: 'In stock'),
            'deal_label' => $price['is_offer'] ? $this->copy(App::getLocale(), 'deal') : '',
            'image' => $images[0] ?? $image,
            'images' => $images,
            'price' => $this->money($price['value']),
            'old_price' => $price['old_value'] ? $this->money($price['old_value']) : '',
            'price_number' => $price['value'],
            'price_note' => $price['note'],
            'price_group' => $price['group'],
            'is_offer' => $price['is_offer'],
            'url' => $this->productUrl((string) ($product->slug ?? $product->id ?? '#')),
            'category' => (string) ($product->category_name ?? ''),
            'brand' => (string) ($product->brand_name ?? ''),
            'description' => (string) ($product->short_description ?? $product->description ?? ''),
            'specs_table' => $this->productSpecsTable($product),
        ];
    }

    protected function productSpecsTable(object $product): array
    {
        $specs = [];
        $raw = (string) ($product->specs ?? '');

        if ($raw !== '') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded)) {
                $raw = (string) ($decoded['html'] ?? $decoded['text'] ?? '');
            }
        }

        if ($raw === '') {
            $raw = (string) ($product->description ?? '');
        }

        $raw = html_entity_decode(strip_tags($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $raw = str_replace(['#010#013', '#013#010', '#010', '#013', "\r\n", "\r"], "\n", $raw);

        foreach (preg_split('/\n+/u', $raw) ?: [] as $line) {
            $line = trim(preg_replace('/\s+/u', ' ', $line) ?: '');

            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode(':', $line, 2));

            if ($name === '' || $value === '') {
                continue;
            }

            $specs[] = ['name' => $name, 'value' => $value];

            if (count($specs) >= 36) {
                break;
            }
        }

        return $specs;
    }

    protected function productImages(int $productId, string $primaryImage = ''): array
    {
        $images = [];

        if ($this->tableReady('product_images')) {
            try {
                $images = DB::table('product_images')
                    ->where('product_id', $productId)
                    ->orderByDesc('is_primary')
                    ->orderByRaw("CASE WHEN source = 'manual' THEN 0 ELSE 1 END")
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->limit(12)
                    ->get()
                    ->map(fn ($image): string => $this->mediaUrl($image->path ?? null, $image->url ?? null))
                    ->filter()
                    ->values()
                    ->all();
            } catch (\Throwable) {
                $images = [];
            }
        }

        if ($primaryImage !== '') {
            array_unshift($images, $primaryImage);
        }

        return array_values(array_unique(array_filter($images)));
    }

    protected function cartCount(Request $request): int
    {
        return array_sum(array_map('intval', $request->session()->get('cart.items', [])));
    }

    protected function cartProductExists(int $productId): bool
    {
        if (! $this->tableReady('products')) {
            return false;
        }

        try {
            return $this->productsQuery()
                ->where('products.id', $productId)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    protected function cartSummary(Request $request): array
    {
        $cart = array_filter(
            $request->session()->get('cart.items', []),
            fn ($quantity, $productId): bool => (int) $productId > 0 && (int) $quantity > 0,
            ARRAY_FILTER_USE_BOTH
        );

        if ($cart === [] || ! $this->tableReady('products')) {
            return ['items' => [], 'subtotal' => 0.0];
        }

        try {
            $products = $this->productsQuery()
                ->whereIn('products.id', array_map('intval', array_keys($cart)))
                ->get()
                ->keyBy('id');

            $items = [];
            $subtotal = 0.0;

            foreach ($cart as $productId => $quantity) {
                $product = $products->get((int) $productId);

                if (! $product) {
                    continue;
                }

                $mapped = $this->mapProduct($product, 'Cart');
                $unitPrice = (float) ($mapped['price_number'] ?? 0);
                $quantity = max(1, min(99, (int) $quantity));
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                $items[] = $mapped + [
                    'quantity' => $quantity,
                    'unit_price_number' => $unitPrice,
                    'line_total' => $this->money($lineTotal),
                    'update_url' => $this->localizedPath(App::getLocale(), 'cart_update', ['product' => (int) $productId]),
                    'remove_url' => $this->localizedPath(App::getLocale(), 'cart_remove', ['product' => (int) $productId]),
                ];
            }

            return ['items' => $items, 'subtotal' => $subtotal];
        } catch (\Throwable) {
            return ['items' => [], 'subtotal' => 0.0];
        }
    }

    protected function productUrl(string $slug): string
    {
        $locale = App::getLocale();
        $prefix = in_array($locale, ['en', 'el'], true) ? '/' . $locale : '/en';

        return $prefix . '/product/' . $slug;
    }

    protected function productsForCategory(string $slug, ?array $category, Request $request): array
    {
        if (! $this->tableReady('products')) {
            return $this->emptyProductResults();
        }

        try {
            $query = $this->productsQuery();
            $ids = $this->categoryIdsForSlug($slug);

            if (Schema::hasColumn('products', 'category_id')) {
                if ($ids !== []) {
                    $query->whereIn('products.category_id', $ids);
                } elseif (Schema::hasTable('categories') && Schema::hasColumn('categories', 'slug')) {
                    $query->where('categories.slug', trim($slug, '/'));
                }
            }

            $selectedFilters = $this->selectedExtraFilters($request);
            $filters = $this->extraFiltersForProducts($query, $selectedFilters);
            $this->applyExtraFilters($query, $selectedFilters);

            $total = $this->countProductQuery($query);
            $pagination = $this->paginationMeta($request, $total);

            $products = $this->orderByImagesFirst($query)
                ->orderByDesc('products.id')
                ->offset($pagination['offset'])
                ->limit($pagination['perPage'])
                ->get()
                ->map(fn ($product): array => $this->mapProduct($product, $category['name'] ?? 'Product'))
                ->all();

            return [
                'items' => $products,
                'total' => $total,
                'pagination' => $pagination,
                'filters' => $filters,
                'selectedFilters' => $selectedFilters,
            ];
        } catch (\Throwable) {
            return $this->emptyProductResults();
        }
    }

    protected function specialProducts(string $type, Request $request): array
    {
        if (! $this->tableReady('products')) {
            return $this->emptyProductResults();
        }

        try {
            $query = $this->productsQuery();

            if ($type === 'outlet') {
                $query->where(function ($builder): void {
                    if ($this->activeOffersReady()) {
                        $builder->whereNotNull('active_offers.product_id');
                    }

                    if (Schema::hasTable('categories')) {
                        if (Schema::hasColumn('categories', 'slug')) {
                            $builder->orWhere('categories.slug', 'like', '%outlet%');
                        }

                        if (Schema::hasColumn('categories', 'name')) {
                            $builder->orWhere('categories.name', 'like', '%OUTLET%');
                        }

                        if (Schema::hasColumn('categories', 'full_path')) {
                            $builder->orWhere('categories.full_path', 'like', '%OUTLET%');
                        }
                    }
                });
            }

            if ($type === 'best_sellers' && $this->tableReady('storefront_order_items')) {
                $sales = DB::table('storefront_order_items')
                    ->select('product_id', DB::raw('sum(quantity) as sold_quantity'))
                    ->whereNotNull('product_id')
                    ->groupBy('product_id');

                $query->leftJoinSub($sales, 'sales_rank', function ($join): void {
                    $join->on('sales_rank.product_id', '=', 'products.id');
                });
            }

            $selectedFilters = $this->selectedExtraFilters($request);
            $filters = $this->extraFiltersForProducts($query, $selectedFilters);
            $this->applyExtraFilters($query, $selectedFilters);

            $total = $this->countProductQuery($query);
            $pagination = $this->paginationMeta($request, $total);

            if ($type === 'best_sellers' && $this->tableReady('storefront_order_items')) {
                $query->orderByRaw('COALESCE(sales_rank.sold_quantity, 0) DESC');
            } else {
                $this->orderByImagesFirst($query);
            }

            $products = $query
                ->orderByDesc('products.id')
                ->offset($pagination['offset'])
                ->limit($pagination['perPage'])
                ->get()
                ->map(fn ($product): array => $this->mapProduct($product, $type === 'outlet' ? $this->copy(App::getLocale(), 'deal') : $this->copy(App::getLocale(), 'best_sellers')))
                ->all();

            return [
                'items' => $products,
                'total' => $total,
                'pagination' => $pagination,
                'filters' => $filters,
                'selectedFilters' => $selectedFilters,
            ];
        } catch (\Throwable) {
            return $this->emptyProductResults();
        }
    }

    protected function searchProducts(string $query, Request $request): array
    {
        if ($query === '') {
            return $this->emptyProductResults();
        }

        if (! $this->tableReady('products')) {
            return $this->emptyProductResults();
        }

        try {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $query) . '%';

            $results = $this->productsQuery()
                ->where(function ($builder) use ($like): void {
                    foreach (['title', 'code', 'mpn', 'ean'] as $column) {
                        if (Schema::hasColumn('products', $column)) {
                            $builder->orWhere('products.' . $column, 'like', $like);
                        }
                    }

                    if (Schema::hasTable('brands') && Schema::hasColumn('brands', 'name')) {
                        $builder->orWhere('brands.name', 'like', $like);
                    }
                });

            $selectedFilters = $this->selectedExtraFilters($request);
            $filters = $this->extraFiltersForProducts($results, $selectedFilters);
            $this->applyExtraFilters($results, $selectedFilters);

            $total = $this->countProductQuery($results);
            $pagination = $this->paginationMeta($request, $total);

            $items = $this->orderByImagesFirst($results)
                ->orderByDesc('products.id')
                ->offset($pagination['offset'])
                ->limit($pagination['perPage'])
                ->get()
                ->map(fn ($product): array => $this->mapProduct($product, 'Result'))
                ->all();

            return [
                'items' => $items,
                'total' => $total,
                'pagination' => $pagination,
                'filters' => $filters,
                'selectedFilters' => $selectedFilters,
            ];
        } catch (\Throwable) {
            return $this->emptyProductResults();
        }
    }

    protected function countProductQuery($query): int
    {
        $countQuery = (clone $query)
            ->cloneWithout(['orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['order']);

        return (int) $countQuery
            ->distinct('products.id')
            ->count('products.id');
    }

    protected function selectedExtraFilters(Request $request): array
    {
        $raw = $request->query('f', []);

        if (! is_array($raw)) {
            return [];
        }

        $selected = [];

        foreach ($raw as $fieldId => $values) {
            if (! ctype_digit((string) $fieldId)) {
                continue;
            }

            $values = is_array($values) ? $values : [$values];
            $values = collect($values)
                ->flatMap(fn ($value): array => is_string($value) ? explode('|', $value) : [])
                ->map(fn (string $value): string => trim($value))
                ->filter()
                ->unique()
                ->take(12)
                ->values()
                ->all();

            if ($values !== []) {
                $selected[(int) $fieldId] = $values;
            }
        }

        return $selected;
    }

    protected function applyExtraFilters($query, array $selectedFilters): void
    {
        if ($selectedFilters === [] || ! $this->extraFieldTablesReady()) {
            return;
        }

        foreach ($selectedFilters as $fieldId => $values) {
            $query->whereExists(function ($exists) use ($fieldId, $values): void {
                $exists->selectRaw('1')
                    ->from('product_extra_field_values as selected_extra_values')
                    ->whereColumn('selected_extra_values.product_id', 'products.id')
                    ->where('selected_extra_values.extra_field_id', (int) $fieldId)
                    ->whereIn('selected_extra_values.value', $values);
            });
        }
    }

    protected function extraFiltersForProducts($baseQuery, array $selectedFilters): array
    {
        if (! $this->extraFieldTablesReady()) {
            return [];
        }

        try {
            $fields = DB::table('product_extra_fields as fields')
                ->leftJoin('product_extra_field_sections as sections', 'sections.id', '=', 'fields.section_id')
                ->where('fields.is_active', true)
                ->where('fields.is_filterable', true)
                ->select([
                    'fields.id',
                    'fields.name',
                    'fields.field_type',
                    'fields.sort_order',
                    'sections.name as section_name',
                    'sections.sort_order as section_sort_order',
                ])
                ->orderBy('sections.sort_order')
                ->orderBy('fields.sort_order')
                ->orderBy('fields.name')
                ->get();

            $filters = [];

            foreach ($fields as $field) {
                $valuesQuery = DB::table('product_extra_field_values as values')
                    ->where('values.extra_field_id', (int) $field->id)
                    ->whereNotNull('values.value')
                    ->where('values.value', '<>', '')
                    ->whereIn('values.product_id', $this->productIdsSubquery($baseQuery))
                    ->select('values.value', DB::raw('count(distinct values.product_id) as products_count'))
                    ->groupBy('values.value')
                    ->orderByDesc('products_count')
                    ->orderBy('values.value')
                    ->limit(12)
                    ->get()
                    ->map(fn ($value): array => [
                        'value' => (string) $value->value,
                        'count' => (int) $value->products_count,
                        'selected' => in_array((string) $value->value, $selectedFilters[(int) $field->id] ?? [], true),
                    ])
                    ->all();

                if ($valuesQuery === []) {
                    continue;
                }

                $filters[] = [
                    'id' => (int) $field->id,
                    'name' => (string) $field->name,
                    'section' => (string) ($field->section_name ?? ''),
                    'type' => (string) ($field->field_type ?? 'text'),
                    'values' => $valuesQuery,
                ];

                if (count($filters) >= 18) {
                    break;
                }
            }

            return $filters;
        } catch (\Throwable) {
            return [];
        }
    }

    protected function productIdsSubquery($query)
    {
        return (clone $query)
            ->cloneWithout(['columns', 'orders', 'limit', 'offset'])
            ->cloneWithoutBindings(['select', 'order'])
            ->select('products.id');
    }

    protected function extraFieldTablesReady(): bool
    {
        return Schema::hasTable('product_extra_fields')
            && Schema::hasTable('product_extra_field_values');
    }

    protected function paginationMeta(Request $request, int $total): array
    {
        $allowedPerPage = [24, 30, 50, 100];
        $perPage = (int) $request->query('per_page', 30);
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 30;
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = max(1, min((int) $request->query('page', 1), $lastPage));
        $from = $total === 0 ? 0 : (($page - 1) * $perPage) + 1;
        $to = min($total, $page * $perPage);

        return [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'lastPage' => $lastPage,
            'offset' => ($page - 1) * $perPage,
            'from' => $from,
            'to' => $to,
            'hasPrevious' => $page > 1,
            'hasNext' => $page < $lastPage,
        ];
    }

    protected function emptyProductResults(): array
    {
        return [
            'items' => [],
            'total' => 0,
            'pagination' => [
                'page' => 1,
                'perPage' => 30,
                'total' => 0,
                'lastPage' => 1,
                'offset' => 0,
                'from' => 0,
                'to' => 0,
                'hasPrevious' => false,
                'hasNext' => false,
            ],
            'filters' => [],
            'selectedFilters' => [],
        ];
    }

    protected function productBySlug(string $slug): array
    {
        if (! $this->tableReady('products')) {
            return $this->emptyProduct($slug);
        }

        try {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $slug) . '%';

            $product = $this->productsQuery()
                ->where(function ($query) use ($slug, $like): void {
                    if (Schema::hasColumn('products', 'slug')) {
                        $query->where('products.slug', $slug);
                        $query->orWhere('products.slug', 'like', $like);
                    }

                    if (is_numeric($slug)) {
                        $query->orWhere('products.id', (int) $slug);
                    }

                    foreach (['code', 'mpn', 'ean'] as $column) {
                        if (Schema::hasColumn('products', $column)) {
                            $query->orWhere('products.' . $column, $slug);
                            $query->orWhere('products.' . $column, 'like', $like);
                        }
                    }

                    if (Schema::hasColumn('products', 'title')) {
                        $query->orWhere('products.title', 'like', $like);
                    }
                })
                ->orderByRaw('CASE WHEN products.slug = ? THEN 0 WHEN products.code = ? THEN 1 WHEN products.mpn = ? THEN 2 ELSE 3 END', [$slug, $slug, $slug])
                ->orderByRaw("CASE WHEN COALESCE(products.image_path, products.main_image, '') = '' THEN 1 ELSE 0 END")
                ->orderByDesc('products.id')
                ->first();

            return $product ? $this->mapProduct($product, 'Product', true) : $this->emptyProduct($slug);
        } catch (\Throwable) {
            return $this->emptyProduct($slug);
        }
    }

    protected function productsQuery()
    {
        $query = DB::table('products');

        if (Schema::hasTable('product_prices')) {
            $query->leftJoin('product_prices', 'product_prices.product_id', '=', 'products.id');
        }

        if ($this->activeOffersReady()) {
            $query->leftJoinSub($this->activeOffersSubquery(), 'active_offers', function ($join): void {
                $join->on('active_offers.product_id', '=', 'products.id');
            });
        }

        if (Schema::hasTable('categories') && Schema::hasColumn('products', 'category_id')) {
            $query->leftJoin('categories', 'categories.id', '=', 'products.category_id');
        }

        if (Schema::hasTable('brands') && Schema::hasColumn('products', 'brand_id')) {
            $query->leftJoin('brands', 'brands.id', '=', 'products.brand_id');
        }

        // if (Schema::hasColumn('products', 'status')) {
        //     $query->whereIn('products.status', ['active', 'enabled', 'yes', '1', 'true']);
        // } elseif (Schema::hasColumn('products', 'active')) {
        //     $query->where('products.active', true);
        // }

        if (Schema::hasColumn('products', 'deleted_at')) {
            $query->whereNull('products.deleted_at');
        }

        return $query->select($this->productSelectColumns());
    }

    protected function productDisplayCode(object $product, string $idColumn = 'id', string $supplierColumn = 'primary_supplier_id', string $fallbackColumn = 'code'): string
    {
        $productId = (int) ($product->{$idColumn} ?? 0);
        $supplierCode = trim((string) (
            $product->{$supplierColumn . '_storefront_code'}
            ?? $product->primary_supplier_storefront_code
            ?? $product->supplier_product_supplier_storefront_code
            ?? $product->{$supplierColumn}
            ?? $product->supplier_id
            ?? $product->supplier_product_supplier_id
            ?? ''
        ));

        if ($productId > 0 && $supplierCode !== '') {
            return (string) $productId . $supplierCode;
        }

        if ($productId > 0) {
            return (string) $productId;
        }

        return (string) ($product->{$fallbackColumn} ?? '');
    }

    protected function orderByImagesFirst($query)
    {
        $imageExpressions = [];

        if (Schema::hasColumn('products', 'image_path')) {
            $imageExpressions[] = "NULLIF(products.image_path, '')";
        }

        if (Schema::hasColumn('products', 'main_image')) {
            $imageExpressions[] = "NULLIF(products.main_image, '')";
        }

        if ($imageExpressions !== []) {
            $query->orderByRaw('CASE WHEN COALESCE(' . implode(', ', $imageExpressions) . ') IS NULL THEN 1 ELSE 0 END');
        }

        return $query;
    }

    protected function categoryBySlug(string $slug): ?array
    {
        if (! $this->tableReady('categories') || ! Schema::hasColumn('categories', 'slug')) {
            return null;
        }

        try {
            $category = $this->categoryRecordForPath($slug);

            if (! $category) {
                return null;
            }

            return [
                'id' => (int) ($category->id ?? 0),
                'parent_id' => isset($category->parent_id) ? (int) $category->parent_id : null,
                'name' => (string) ($category->name ?? 'Category'),
                'slug' => (string) ($category->slug ?? $slug),
                'full_path' => (string) ($category->full_path ?? $category->name ?? ''),
                'url_path' => $this->categoryUrlPath($category),
                'image' => $this->mediaUrl($category->category_image ?? null, null),
                'count' => null,
                'children' => $this->categoryChildren((int) ($category->id ?? 0)),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    protected function categoryChildren(int $categoryId): array
    {
        if ($categoryId <= 0 || ! $this->tableReady('categories') || ! Schema::hasColumn('categories', 'parent_id')) {
            return [];
        }

        try {
            $query = DB::table('categories')->where('parent_id', $categoryId);

            if (Schema::hasColumn('categories', 'active')) {
                $query->where('active', true);
            }

            return $query
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(24)
                ->get()
                ->map(fn ($category): array => [
                    'id' => (int) ($category->id ?? 0),
                    'parent_id' => isset($category->parent_id) ? (int) $category->parent_id : null,
                    'name' => (string) ($category->name ?? 'Category'),
                    'slug' => (string) ($category->slug ?? '#'),
                    'full_path' => (string) ($category->full_path ?? $category->name ?? ''),
                    'url_path' => $this->categoryUrlPath($category),
                    'image' => $this->mediaUrl($category->category_image ?? null, null),
                    'count' => null,
                    'children' => [],
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    protected function productSelectColumns(): array
    {
        $select = ['products.*'];

        foreach (['retail_inc_vat', 'discount_inc_vat', 'wholesale_inc_vat'] as $column) {
            if (Schema::hasTable('product_prices') && Schema::hasColumn('product_prices', $column)) {
                $select[] = 'product_prices.' . $column;
            }
        }

        if ($this->activeOffersReady()) {
            $select[] = DB::raw('active_offers.offer_price as active_offer_price');
            $select[] = DB::raw('active_offers.regular_price as active_offer_regular_price');
            $select[] = DB::raw('active_offers.discount_percent as active_offer_discount_percent');
            $select[] = DB::raw('active_offers.badge as active_offer_badge');
        }

        $select[] = Schema::hasTable('categories') && Schema::hasColumn('categories', 'name')
            ? DB::raw('categories.name as category_name')
            : DB::raw("'' as category_name");

        $select[] = Schema::hasTable('categories') && Schema::hasColumn('categories', 'full_path')
            ? DB::raw('categories.full_path as category_full_path')
            : DB::raw("'' as category_full_path");

        $select[] = Schema::hasTable('brands') && Schema::hasColumn('brands', 'name')
            ? DB::raw('brands.name as brand_name')
            : DB::raw("'' as brand_name");

        if (Schema::hasTable('suppliers') && Schema::hasColumn('products', 'primary_supplier_id')) {
            $select[] = DB::raw('(select ' . $this->supplierStorefrontCodeExpression('suppliers') . ' from suppliers where suppliers.id = products.primary_supplier_id limit 1) as primary_supplier_storefront_code');
        } else {
            $select[] = DB::raw('NULL as primary_supplier_storefront_code');
        }

        if (Schema::hasTable('supplier_products') && Schema::hasColumn('supplier_products', 'matched_product_id')) {
            $select[] = DB::raw('(select supplier_products.supplier_id from supplier_products where supplier_products.matched_product_id = products.id order by supplier_products.last_seen_at desc, supplier_products.id desc limit 1) as supplier_product_supplier_id');
            $select[] = DB::raw('(select ' . $this->supplierStorefrontCodeExpression('latest_suppliers') . ' from supplier_products join suppliers as latest_suppliers on latest_suppliers.id = supplier_products.supplier_id where supplier_products.matched_product_id = products.id order by supplier_products.last_seen_at desc, supplier_products.id desc limit 1) as supplier_product_supplier_storefront_code');
        } else {
            $select[] = DB::raw('NULL as supplier_product_supplier_id');
            $select[] = DB::raw('NULL as supplier_product_supplier_storefront_code');
        }

        return $select;
    }

    protected function supplierStorefrontCodeSelect(string $tableAlias, string $alias)
    {
        return DB::raw($this->supplierStorefrontCodeExpression($tableAlias) . ' as ' . $alias);
    }

    protected function supplierStorefrontCodeExpression(string $tableAlias): string
    {
        if (Schema::hasTable('suppliers') && Schema::hasColumn('suppliers', 'storefront_code')) {
            return 'coalesce(nullif(' . $tableAlias . '.storefront_code, \'\'), cast(' . $tableAlias . '.id as char))';
        }

        return 'cast(' . $tableAlias . '.id as char)';
    }

    protected function categoryRecordForPath(string $path): ?object
    {
        $path = trim($path, '/');

        if ($path === '' || ! $this->tableReady('categories')) {
            return null;
        }

        $query = DB::table('categories');

        if (Schema::hasColumn('categories', 'active')) {
            $query->where('active', true);
        }

        $categories = $query->get();

        foreach ($categories as $category) {
            if ($this->categoryUrlPath($category) === $path) {
                return $category;
            }
        }

        if (Schema::hasColumn('categories', 'slug')) {
            $fallback = DB::table('categories');

            if (Schema::hasColumn('categories', 'active')) {
                $fallback->where('active', true);
            }

            return $fallback->where('slug', $path)->first();
        }

        return null;
    }

    protected function categoryUrlPath(object|array $category): string
    {
        $fullPath = trim((string) data_get($category, 'full_path', ''));

        if ($fullPath !== '') {
            $parts = collect(preg_split('/\s*>\s*/u', $fullPath) ?: [])
                ->map(fn (string $part): string => $this->slugify($part))
                ->filter()
                ->values()
                ->all();

            if ($parts !== []) {
                return implode('/', $parts);
            }
        }

        $slug = trim((string) data_get($category, 'slug', ''), '/');

        return $slug !== '' && $slug !== '#'
            ? $slug
            : $this->slugify((string) data_get($category, 'name', 'category'));
    }

    protected function categoryIdsForSlug(string $slug): array
    {
        if (! $this->tableReady('categories')) {
            return [];
        }

        try {
            $category = $this->categoryRecordForPath($slug);

            if (! $category) {
                return [];
            }

            $ids = [(int) $category->id];

            if (Schema::hasColumn('categories', 'parent_id')) {
                $ids = array_merge($ids, $this->categoryDescendantIds((int) $category->id));
            }

            if (Schema::hasColumn('categories', 'full_path') && ! empty($category->full_path)) {
                $pathIds = DB::table('categories')
                    ->where('full_path', 'like', $category->full_path . ' >%')
                    ->pluck('id')
                    ->map(fn ($id): int => (int) $id)
                    ->all();

                $ids = array_merge($ids, $pathIds);
            }

            return array_values(array_unique($ids));
        } catch (\Throwable) {
            return [];
        }
    }

    protected function categoryDescendantIds(int $categoryId): array
    {
        $ids = [];
        $queue = [$categoryId];
        $guard = 0;

        while ($queue !== [] && $guard < 5000) {
            $parentId = array_shift($queue);
            $children = DB::table('categories')
                ->where('parent_id', $parentId)
                ->pluck('id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            foreach ($children as $childId) {
                if (! in_array($childId, $ids, true)) {
                    $ids[] = $childId;
                    $queue[] = $childId;
                }
            }

            $guard += count($children) + 1;
        }

        return $ids;
    }

    protected function findCategoryInTree(array $categories, string $slug): ?array
    {
        $slug = trim($slug, '/');

        foreach ($categories as $category) {
            if (($category['url_path'] ?? '') === $slug || ($category['slug'] ?? '') === $slug) {
                return $category;
            }

            $child = $this->findCategoryInTree($category['children'] ?? [], $slug);

            if ($child) {
                return $child;
            }
        }

        return null;
    }

    protected function tableReady(string $table): bool
    {
        if (! $this->databaseReachable()) {
            return false;
        }

        try {
            return Schema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    protected function databaseReachable(): bool
    {
        if ($this->databaseReachable !== null) {
            return $this->databaseReachable;
        }

        $host = (string) config('database.connections.' . config('database.default') . '.host', '');

        if ($host !== '' && ! in_array($host, ['127.0.0.1', 'localhost', '::1'], true) && gethostbyname($host) === $host) {
            return $this->databaseReachable = false;
        }

        try {
            DB::connection()->getPdo();

            return $this->databaseReachable = true;
        } catch (\Throwable) {
            return $this->databaseReachable = false;
        }
    }

    protected function mediaUrl(?string $path, ?string $url): string
    {
        $value = $path ?: $url;

        if (! $value) {
            return '';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        return '/media/' . ltrim($value, '/');
    }

    protected function priceContext(): array
    {
        $group = $this->currentCustomerGroup();

        return [
            'group' => $group,
            'label' => $group === 'b2b' ? $this->copy(App::getLocale(), 'b2b_active') : 'Retail',
            'note' => $group === 'b2b' ? $this->copy(App::getLocale(), 'tax_note_b2b') : $this->copy(App::getLocale(), 'tax_note_retail'),
        ];
    }

    protected function currentCustomerGroup(): string
    {
        $user = request()->user();

        return $user && ($user->customer_group ?? 'retail') === 'b2b' ? 'b2b' : 'retail';
    }

    protected function productPriceForCurrentCustomer(object $product): array
    {
        $activeOffer = $this->firstProductPrice($product, ['active_offer_price']);
        $activeOfferRegular = $this->firstProductPrice($product, ['active_offer_regular_price', 'retail_inc_vat']);

        if ($activeOffer !== null) {
            $offerPrices = $this->offerPricesForCurrentCustomer($activeOffer, $activeOfferRegular, $product->vat_rate ?? null);

            return [
                'group' => $this->currentCustomerGroup(),
                'value' => $offerPrices['offer'],
                'old_value' => $offerPrices['regular'],
                'note' => $this->currentCustomerGroup() === 'b2b'
                    ? $this->copy(App::getLocale(), 'tax_note_b2b')
                    : $this->copy(App::getLocale(), 'tax_note_retail'),
                'is_offer' => true,
            ];
        }

        if ($this->currentCustomerGroup() === 'b2b') {
            $wholesale = $this->firstProductPrice($product, ['wholesale_inc_vat']);
            $fallback = $this->firstProductPrice($product, ['discount_inc_vat', 'retail_inc_vat']);

            return [
                'group' => 'b2b',
                'value' => $wholesale ?? $this->priceExVat($fallback, $product->vat_rate ?? null),
                'old_value' => null,
                'note' => $this->copy(App::getLocale(), 'tax_note_b2b'),
                'is_offer' => false,
            ];
        }

        $discount = $this->firstProductPrice($product, ['discount_inc_vat']);
        $retail = $this->firstProductPrice($product, ['retail_inc_vat']);

        return [
            'group' => 'retail',
            'value' => $discount ?? $retail,
            'old_value' => $discount !== null ? $retail : null,
            'note' => $this->copy(App::getLocale(), 'tax_note_retail'),
            'is_offer' => $discount !== null,
        ];
    }

    protected function offerPricesForCurrentCustomer($offerPrice, $regularPrice, $vatRate = null): array
    {
        $offer = $offerPrice === null || $offerPrice === '' ? null : (float) $offerPrice;
        $regular = $regularPrice === null || $regularPrice === '' ? null : (float) $regularPrice;

        if ($this->currentCustomerGroup() === 'b2b') {
            $offer = $this->priceExVat($offer, $vatRate);
            $regular = $this->priceExVat($regular, $vatRate);
        }

        return [
            'offer' => $offer,
            'regular' => $regular !== null && $offer !== null && $regular > $offer ? $regular : null,
        ];
    }

    protected function priceExVat(?float $price, $vatRate = null): ?float
    {
        if ($price === null) {
            return null;
        }

        $rate = $vatRate === null || $vatRate === '' ? 19.0 : (float) $vatRate;
        $rate = $rate > 1 ? $rate / 100 : $rate;

        if ($rate <= 0) {
            return $price;
        }

        return round($price / (1 + $rate), 4);
    }

    protected function firstProductPrice(object $product, array $columns): ?float
    {
        foreach ($columns as $column) {
            if (property_exists($product, $column) && $product->{$column} !== null && $product->{$column} !== '') {
                return (float) $product->{$column};
            }
        }

        return null;
    }

    protected function activeOffersReady(): bool
    {
        return Schema::hasTable('shop_offers')
            && Schema::hasColumn('shop_offers', 'product_id')
            && Schema::hasColumn('shop_offers', 'offer_price');
    }

    protected function activeOffersSubquery()
    {
        return DB::table('shop_offers')
            ->select(
                'product_id',
                DB::raw('MIN(offer_price) as offer_price'),
                DB::raw('MIN(regular_price) as regular_price'),
                DB::raw('MAX(discount_percent) as discount_percent'),
                DB::raw('MAX(badge) as badge'),
            )
            ->whereNotNull('product_id')
            ->where('is_active', true)
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->groupBy('product_id');
    }

    protected function bannerMediaUrl(?string $path, ?string $url): string
    {
        $url = trim((string) $url);

        if ($url !== '') {
            return $url;
        }

        return $this->mediaUrl($path, null);
    }

    protected function money($value): string
    {
        return $value === null ? 'Call' : number_format((float) $value, 2, '.', ',') . ' €';
    }

    protected function percent($value): string
    {
        return $value === null ? '' : '-' . rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') . '%';
    }

    protected function categoryTreeFromPaths(array $categories): array
    {
        $groups = [];
        $rootCategories = [];

        foreach ($categories as $category) {
            $path = trim($category['full_path'] ?: $category['name']);

            if (! str_contains($path, '>')) {
                $rootCategories[mb_strtolower(trim($category['name']))] = $category;
            }
        }

        foreach ($categories as $category) {
            $path = trim($category['full_path'] ?: $category['name']);

            if (! str_contains($path, '>')) {
                continue;
            }

            $parts = array_values(array_filter(array_map('trim', explode('>', $path))));

            if ($parts === []) {
                continue;
            }

            $parentName = $parts[0];
            $parentKey = $this->slugify($parentName);
            $rootCategory = $rootCategories[mb_strtolower($parentName)] ?? null;

            if (! isset($groups[$parentKey])) {
                $groups[$parentKey] = [
                    'id' => (int) ($rootCategory['id'] ?? $category['parent_id'] ?? $category['id'] ?? count($groups) + 1),
                    'parent_id' => null,
                    'name' => (string) ($rootCategory['name'] ?? $parentName),
                    'slug' => (string) ($rootCategory['slug'] ?? $parentKey),
                    'full_path' => (string) ($rootCategory['full_path'] ?? $parentName),
                    'image' => (string) ($rootCategory['image'] ?? ''),
                    'count' => 0,
                    'children' => [],
                ];
            }

            $groups[$parentKey]['count']++;

            if (! isset($parts[1])) {
                continue;
            }

            $childName = $parts[1];
            $childKey = $this->slugify($parentName . '-' . $childName);

            if (! isset($groups[$parentKey]['children'][$childKey])) {
                $groups[$parentKey]['children'][$childKey] = [
                    'id' => count($groups[$parentKey]['children']) + 1,
                    'parent_id' => $groups[$parentKey]['id'],
                    'name' => $childName,
                    'slug' => $category['slug'] !== '#' ? $category['slug'] : $childKey,
                    'full_path' => $parentName . ' > ' . $childName,
                    'image' => $category['image'],
                    'count' => 0,
                    'children' => [],
                ];
            }

            $groups[$parentKey]['children'][$childKey]['count']++;
        }

        usort($groups, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        return array_map(function (array $group): array {
            $children = array_values($group['children']);
            usort($children, fn (array $a, array $b): int => $b['count'] <=> $a['count']);
            $group['children'] = array_slice($children, 0, 14);

            return $group;
        }, array_slice($groups, 0, 14));
    }

    protected function slugify(string $value): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'category';
    }

    protected function buildCategoryTree(array $categories): array
    {
        $byId = [];
        $childrenByParent = [];

        foreach ($categories as $category) {
            $category['children'] = [];
            $byId[$category['id']] = $category;
            $childrenByParent[$category['parent_id'] ?? 0][] = $category['id'];
        }

        $build = function (int $id) use (&$build, &$byId, &$childrenByParent): array {
            $category = $byId[$id];

            foreach ($childrenByParent[$id] ?? [] as $childId) {
                if (isset($byId[$childId])) {
                    $category['children'][] = $build($childId);
                }
            }

            return $category;
        };

        $tree = [];

        foreach ($byId as $id => $category) {
            if ($category['parent_id'] === null || ! isset($byId[$category['parent_id']])) {
                $tree[] = $build($id);
            }
        }

        return $tree;
    }

}
