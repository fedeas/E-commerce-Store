<?php

namespace App\Filament\Resources\ProductPrices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'title')
                    ->required(),
                TextInput::make('cost_ex_vat')
                    ->numeric(),
                TextInput::make('retail_inc_vat')
                    ->numeric(),
                TextInput::make('discount_inc_vat')
                    ->numeric(),
                TextInput::make('skroutz_inc_vat')
                    ->numeric(),
                TextInput::make('wholesale_inc_vat')
                    ->numeric(),
                TextInput::make('silver_inc_vat')
                    ->numeric(),
                TextInput::make('gold_inc_vat')
                    ->numeric(),
            ]);
    }
}
