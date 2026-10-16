<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                TextInput::make('storefront_code')
                    ->label('Storefront code')
                    ->maxLength(12)
                    ->helperText('Used as the suffix in storefront product codes. Example: product 102302 + code 22 = 10230222.'),
                Toggle::make('active')
                    ->default(true),
            ]);
    }
}
