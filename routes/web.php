<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])
    ->name('storefront.home');

Route::get('/category/{slug}', [StorefrontController::class, 'category'])
    ->where('slug', '.*')
    ->name('storefront.category');

Route::get('/search', [StorefrontController::class, 'search'])
    ->name('storefront.search');

Route::get('/outlet', [StorefrontController::class, 'outlet'])
    ->name('storefront.outlet');

Route::get('/best-sellers', [StorefrontController::class, 'bestSellers'])
    ->name('storefront.best-sellers');

Route::get('/product/{slug}', [StorefrontController::class, 'product'])
    ->name('storefront.product');

Route::get('/contact', [StorefrontController::class, 'contact'])
    ->name('storefront.contact');

Route::post('/contact', [StorefrontController::class, 'sendContact'])
    ->name('storefront.contact.send');

Route::get('/shipping', [StorefrontController::class, 'shipping'])
    ->name('storefront.shipping');

Route::get('/faq', [StorefrontController::class, 'faq'])
    ->name('storefront.faq');

Route::get('/cookie-policy', [StorefrontController::class, 'cookiePolicy'])
    ->name('storefront.cookie-policy');

Route::get('/privacy-policy', [StorefrontController::class, 'privacyPolicy'])
    ->name('storefront.privacy-policy');

Route::get('/terms-and-conditions', [StorefrontController::class, 'termsAndConditions'])
    ->name('storefront.terms-and-conditions');

Route::get('/pages/{slug}', [StorefrontController::class, 'contentPage'])
    ->whereIn('slug', ['shipping', 'tropoi-apostolis', 'faq', 'syxnes-erwtiseis', 'cookie-policy', 'politiki-cookies', 'privacy-policy', 'politiki-aporritoy', 'terms-and-conditions', 'oroi-kai-proypotheseis'])
    ->name('storefront.page');

Route::get('/tropoi-apostolis', [StorefrontController::class, 'shipping'])
    ->name('storefront.tropoi-apostolis');

Route::get('/syxnes-erwtiseis', [StorefrontController::class, 'faq'])
    ->name('storefront.syxnes-erwtiseis');

Route::get('/politiki-cookies', [StorefrontController::class, 'cookiePolicy'])
    ->name('storefront.politiki-cookies');

Route::get('/politiki-aporritoy', [StorefrontController::class, 'privacyPolicy'])
    ->name('storefront.politiki-aporritoy');

Route::get('/oroi-kai-proypotheseis', [StorefrontController::class, 'termsAndConditions'])
    ->name('storefront.oroi-kai-proypotheseis');

Route::get('/media/{path}', [StorefrontController::class, 'publicMedia'])
    ->where('path', '.*')
    ->name('storefront.media');

Route::get('/cart', [StorefrontController::class, 'cart'])
    ->name('storefront.cart');

Route::get('/checkout', [StorefrontController::class, 'checkout'])
    ->name('storefront.checkout');

Route::post('/checkout', [StorefrontController::class, 'submitCheckout'])
    ->name('storefront.checkout.store');

Route::post('/cart/items', [StorefrontController::class, 'addToCart'])
    ->name('storefront.cart.add');

Route::patch('/cart/items/{product}', [StorefrontController::class, 'updateCartItem'])
    ->name('storefront.cart.update');

Route::delete('/cart/items/{product}', [StorefrontController::class, 'removeCartItem'])
    ->name('storefront.cart.remove');

Route::prefix('{locale}')
    ->whereIn('locale', ['en', 'el'])
    ->group(function (): void {
        Route::get('/', [StorefrontController::class, 'home'])
            ->name('storefront.localized.home');

        Route::get('/category/{slug}', [StorefrontController::class, 'category'])
            ->where('slug', '.*')
            ->name('storefront.localized.category');

        Route::get('/search', [StorefrontController::class, 'search'])
            ->name('storefront.localized.search');

        Route::get('/outlet', [StorefrontController::class, 'outlet'])
            ->name('storefront.localized.outlet');

        Route::get('/best-sellers', [StorefrontController::class, 'bestSellers'])
            ->name('storefront.localized.best-sellers');

        Route::get('/product/{slug}', [StorefrontController::class, 'product'])
            ->name('storefront.localized.product');

        Route::get('/contact', [StorefrontController::class, 'contact'])
            ->name('storefront.localized.contact');

        Route::post('/contact', [StorefrontController::class, 'sendContact'])
            ->name('storefront.localized.contact.send');

        Route::get('/shipping', [StorefrontController::class, 'shipping'])
            ->name('storefront.localized.shipping');

        Route::get('/faq', [StorefrontController::class, 'faq'])
            ->name('storefront.localized.faq');

        Route::get('/cookie-policy', [StorefrontController::class, 'cookiePolicy'])
            ->name('storefront.localized.cookie-policy');

        Route::get('/privacy-policy', [StorefrontController::class, 'privacyPolicy'])
            ->name('storefront.localized.privacy-policy');

        Route::get('/terms-and-conditions', [StorefrontController::class, 'termsAndConditions'])
            ->name('storefront.localized.terms-and-conditions');

        Route::get('/pages/{slug}', [StorefrontController::class, 'contentPage'])
            ->whereIn('slug', ['shipping', 'tropoi-apostolis', 'faq', 'syxnes-erwtiseis', 'cookie-policy', 'politiki-cookies', 'privacy-policy', 'politiki-aporritoy', 'terms-and-conditions', 'oroi-kai-proypotheseis'])
            ->name('storefront.localized.page');

        Route::get('/tropoi-apostolis', [StorefrontController::class, 'shipping'])
            ->name('storefront.localized.tropoi-apostolis');

        Route::get('/syxnes-erwtiseis', [StorefrontController::class, 'faq'])
            ->name('storefront.localized.syxnes-erwtiseis');

        Route::get('/politiki-cookies', [StorefrontController::class, 'cookiePolicy'])
            ->name('storefront.localized.politiki-cookies');

        Route::get('/politiki-aporritoy', [StorefrontController::class, 'privacyPolicy'])
            ->name('storefront.localized.politiki-aporritoy');

        Route::get('/oroi-kai-proypotheseis', [StorefrontController::class, 'termsAndConditions'])
            ->name('storefront.localized.oroi-kai-proypotheseis');

        Route::get('/cart', [StorefrontController::class, 'cart'])
            ->name('storefront.localized.cart');

        Route::get('/checkout', [StorefrontController::class, 'checkout'])
            ->name('storefront.localized.checkout');

        Route::post('/checkout', [StorefrontController::class, 'submitCheckout'])
            ->name('storefront.localized.checkout.store');

        Route::middleware('auth')->group(function (): void {
            Route::get('/b2b-request', [StorefrontController::class, 'b2bRequest'])
                ->name('storefront.localized.b2b-request');

            Route::post('/b2b-request', [StorefrontController::class, 'submitB2bRequest'])
                ->name('storefront.localized.b2b-request.store');
        });

        Route::post('/cart/items', [StorefrontController::class, 'addToCart'])
            ->name('storefront.localized.cart.add');

        Route::patch('/cart/items/{product}', [StorefrontController::class, 'updateCartItem'])
            ->name('storefront.localized.cart.update');

        Route::delete('/cart/items/{product}', [StorefrontController::class, 'removeCartItem'])
            ->name('storefront.localized.cart.remove');

        Route::middleware('guest')->group(function (): void {
            Route::get('/login', [AuthenticatedSessionController::class, 'create'])
                ->name('storefront.localized.login');

            Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->name('storefront.localized.login.store');

            Route::get('/register', [RegisteredUserController::class, 'create'])
                ->name('storefront.localized.register');

            Route::post('/register', [RegisteredUserController::class, 'store'])
                ->name('storefront.localized.register.store');
        });

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth')
            ->name('storefront.localized.logout');
    });

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
