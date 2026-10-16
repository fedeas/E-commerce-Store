<?php

namespace App\Filament\Resources\ProductPrices;

use App\Filament\Resources\ProductPrices\Pages\CreateProductPrice;
use App\Filament\Resources\ProductPrices\Pages\EditProductPrice;
use App\Filament\Resources\ProductPrices\Pages\ListProductPrices;
use App\Filament\Resources\ProductPrices\Schemas\ProductPriceForm;
use App\Filament\Resources\ProductPrices\Tables\ProductPricesTable;
use App\Models\ProductPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductPriceResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = ProductPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return ProductPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductPricesTable::configure($table);
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
            'index' => ListProductPrices::route('/'),
            'create' => CreateProductPrice::route('/create'),
            'edit' => EditProductPrice::route('/{record}/edit'),
        ];
    }
}
