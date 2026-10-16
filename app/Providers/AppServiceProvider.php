<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_START,
            fn (): View => view('filament.sidebar.catalog-menu'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): View => view('filament.sidebar.custom-styles'),
        );
    }
}