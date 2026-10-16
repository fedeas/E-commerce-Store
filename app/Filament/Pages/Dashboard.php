<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Catalog\CategoriesProducts;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static bool $shouldRegisterNavigation = false;

    public function mount(): void
    {
        $this->redirect(CategoriesProducts::getUrl(), navigate: true);
    }
}