<?php

namespace App\Filament\Resources\SupplierProducts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SupplierProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->relationship('supplier', 'id')
                    ->required(),
                Select::make('matched_product_id')
                    ->relationship('matchedProduct', 'title'),
                TextInput::make('supplier_sku'),
                TextInput::make('mpn'),
                TextInput::make('ean'),
                TextInput::make('brand_name'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('category_raw'),
                TextInput::make('cost_ex_vat')
                    ->numeric(),
                TextInput::make('retail_inc_vat')
                    ->numeric(),
                TextInput::make('stock_quantity')
                    ->numeric(),
                TextInput::make('availability'),
                TextInput::make('images'),
                TextInput::make('raw_data'),
                TextInput::make('match_status')
                    ->required()
                    ->default('unmatched'),
                DateTimePicker::make('last_seen_at'),
            ]);
    }
}
