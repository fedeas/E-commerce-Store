<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class QuickAddCategories extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Γρήγορη προσθήκη κατηγοριών';
    protected static ?string $title = 'Γρήγορη προσθήκη κατηγοριών';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-folder-plus';
    protected static ?int $navigationSort = 21;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
