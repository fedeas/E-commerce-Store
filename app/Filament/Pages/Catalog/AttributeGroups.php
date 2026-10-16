<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class AttributeGroups extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Ομάδες ιδιοτήτων';
    protected static ?string $title = 'Ομάδες ιδιοτήτων';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?int $navigationSort = 12;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
