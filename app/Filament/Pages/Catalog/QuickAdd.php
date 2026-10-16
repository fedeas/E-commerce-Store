<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class QuickAdd extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Γρήγορη προσθήκη';
    protected static ?string $title = 'Γρήγορη προσθήκη';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
