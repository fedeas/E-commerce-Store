@php
    $url = $product['url'] ?? '#';
    $title = $product['title'] ?? 'Product';
    $displayCode = $product['display_code'] ?? ($product['code'] ?? '');
    $label = $product['label'] ?? '';
    $image = $product['image'] ?? '';
    $price = $product['price'] ?? '';
    $oldPrice = $product['old_price'] ?? '';
    $dealLabel = trim((string) ($product['deal_label'] ?? ($copy['deal'] ?? 'Deal')));
    $brand = trim((string) ($product['brand'] ?? ''));
    $category = trim((string) ($product['category'] ?? ''));
    $mpn = trim((string) ($product['mpn'] ?? ''));
    $copy = $copy ?? [];
    $cartAddUrl = $cartAddUrl ?? '/cart/items';
    $hasDiscount = trim((string) $oldPrice) !== '';
    $priceNote = trim((string) ($product['price_note'] ?? ''));
    $initials = collect(preg_split('/\s+/', trim($brand ?: $title)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_substr($word, 0, 1))
        ->implode('');
@endphp

<div class="product-card" aria-label="{{ $title }}">
    <div class="product-media">
        @if($label)
            <span class="product-label">{{ $label }}</span>
        @endif

        @if($hasDiscount)
            <span class="product-save">{{ $dealLabel ?: ($copy['deal'] ?? 'Deal') }}</span>
        @endif

        @if($image)
            <a href="{{ $url }}" aria-label="{{ $title }}">
                <img src="{{ $image }}" alt="{{ $title }}" loading="lazy">
            </a>
        @else
            <a href="{{ $url }}" class="product-placeholder" aria-label="{{ $title }}">
                <strong>{{ $initials ?: 'CL' }}</strong>
                <span>{{ $copy['image_pending'] ?? 'Image pending' }}</span>
            </a>
        @endif
    </div>

    <div class="product-body">
        <div class="sku-row">
            <span class="sku">{{ $displayCode ?: ($copy['sku_on_request'] ?? 'SKU on request') }}</span>
            <span class="stock-dot">{{ $copy['available'] ?? 'Available' }}</span>
        </div>

        <h3 class="product-title"><a href="{{ $url }}">{{ $title }}</a></h3>

        <div class="product-meta">
            @if($brand)
                <span>{{ $brand }}</span>
            @endif

            @if($category)
                <span>{{ $category }}</span>
            @endif

            @if($mpn)
                <span>{{ $mpn }}</span>
            @endif
        </div>

        <div class="price-row">
            <div class="price">
                <strong>{{ $price }}</strong>
                @if($oldPrice)
                    <span>{{ $oldPrice }}</span>
                @else
                    <span></span>
                @endif
                @if($priceNote)
                    <small class="price-note">{{ $priceNote }}</small>
                @endif
            </div>

            <form method="post" action="{{ $cartAddUrl }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product['id'] ?? 0 }}">
                <input type="hidden" name="quantity" value="1">
                <button class="add-btn" type="submit">{{ $copy['add_to_cart'] ?? 'Add' }}</button>
            </form>
        </div>
    </div>
</div>
