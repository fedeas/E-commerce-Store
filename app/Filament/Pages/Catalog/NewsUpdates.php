<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class NewsUpdates extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Νέα και ενημέρωση';
    protected static ?string $title = 'Νέα και ενημέρωση';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-newspaper';
    protected static ?int $navigationSort = 24;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
