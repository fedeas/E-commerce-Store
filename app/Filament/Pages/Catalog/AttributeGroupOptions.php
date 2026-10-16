<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class AttributeGroupOptions extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Επιλογές ομάδων ιδιοτήτων';
    protected static ?string $title = 'Επιλογές ομάδων ιδιοτήτων';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?int $navigationSort = 13;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
