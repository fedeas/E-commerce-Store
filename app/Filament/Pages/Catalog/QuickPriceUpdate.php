<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class QuickPriceUpdate extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Γρήγορη ανανέωση τιμών';
    protected static ?string $title = 'Γρήγορη ανανέωση τιμών';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 17;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
