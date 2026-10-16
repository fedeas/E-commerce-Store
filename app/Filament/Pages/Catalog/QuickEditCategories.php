<?php

namespace App\Filament\Pages\Catalog;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class QuickEditCategories extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string | UnitEnum | null $navigationGroup = 'Κεντρικό Μενού';

    protected static ?string $navigationParentItem = 'Κατάλογος';


    protected static ?string $navigationLabel = 'Γρήγορη επεξεργασία κατηγοριών';
    protected static ?string $title = 'Γρήγορη επεξεργασία κατηγοριών';
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-pencil-square';
    protected static ?int $navigationSort = 22;

    protected string $view = 'filament.pages.catalog.simple-placeholder';
}
