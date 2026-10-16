<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class StockTexts extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Λεκτικά stock';
    protected static ?string $title = 'Λεκτικά stock';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 14;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
