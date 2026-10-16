<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class RetailTool extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Retail Tool';
    protected static ?string $title = 'Retail Tool';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int $navigationSort = 32;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
