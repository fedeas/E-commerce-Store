<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class CancelledProducts extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Ακυρωμένα προϊόντα';
    protected static ?string $title = 'Ακυρωμένα προϊόντα';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-x-circle';
    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
