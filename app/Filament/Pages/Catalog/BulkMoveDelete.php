<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class BulkMoveDelete extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Μαζική μετακίνηση ή διαγραφή';
    protected static ?string $title = 'Μαζική μετακίνηση ή διαγραφή';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?int $navigationSort = 19;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
