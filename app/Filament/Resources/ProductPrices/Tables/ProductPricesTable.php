<?php

namespace App\Filament\Resources\ProductPrices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductPricesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.title')
                    ->searchable(),
                TextColumn::make('cost_ex_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('retail_inc_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_inc_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('skroutz_inc_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('wholesale_inc_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('silver_inc_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('gold_inc_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
