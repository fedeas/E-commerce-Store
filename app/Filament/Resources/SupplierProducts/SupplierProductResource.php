<?php

namespace App\Filament\Resources\SupplierProducts;

use App\Filament\Resources\SupplierProducts\Pages\CreateSupplierProduct;
use App\Filament\Resources\SupplierProducts\Pages\EditSupplierProduct;
use App\Filament\Resources\SupplierProducts\Pages\ListSupplierProducts;
use App\Filament\Resources\SupplierProducts\Schemas\SupplierProductForm;
use App\Filament\Resources\SupplierProducts\Tables\SupplierProductsTable;
use App\Models\SupplierProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierProductResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = SupplierProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return SupplierProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSupplierProducts::route('/'),
            'create' => CreateSupplierProduct::route('/create'),
            'edit' => EditSupplierProduct::route('/{record}/edit'),
        ];
    }
}
