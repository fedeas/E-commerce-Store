<?php

namespace App\Filament\Pages\Catalog;

use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class CatalogDashboard extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationLabel = 'Κατάλογος';

    protected static ?string $title = 'Κατάλογος';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-folder';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.catalog.catalog-dashboard';
}
