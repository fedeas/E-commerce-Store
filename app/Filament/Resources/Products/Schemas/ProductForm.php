<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code'),
                TextInput::make('mpn'),
                TextInput::make('ean'),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Select::make('brand_id')
                    ->relationship('brand', 'name'),
                Select::make('category_id')
                    ->relationship('category', 'name'),
                Select::make('primary_supplier_id')
                    ->relationship('primarySupplier', 'id'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('vat_rate')
                    ->required()
                    ->numeric()
                    ->default(19.0),
                Textarea::make('short_description')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('specs'),
                TextInput::make('weight_kg')
                    ->numeric(),
                TextInput::make('dimensions'),
                FileUpload::make('main_image')
                    ->image(),
                TextInput::make('meta_description'),
                TextInput::make('meta_keywords'),
                DateTimePicker::make('last_imported_at'),
            ]);
    }
}
