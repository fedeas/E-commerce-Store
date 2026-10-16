<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class Installments extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Σε άτοκες δόσεις';
    protected static ?string $title = 'Σε άτοκες δόσεις';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 29;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
