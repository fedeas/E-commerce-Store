<?php

namespace App\Filament\Resources\SupplierProducts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SupplierProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.id')
                    ->searchable(),
                TextColumn::make('matchedProduct.title')
                    ->searchable(),
                TextColumn::make('supplier_sku')
                    ->searchable(),
                TextColumn::make('mpn')
                    ->searchable(),
                TextColumn::make('ean')
                    ->searchable(),
                TextColumn::make('brand_name')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('category_raw')
                    ->searchable(),
                TextColumn::make('cost_ex_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('retail_inc_vat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('availability')
                    ->searchable(),
                TextColumn::make('match_status')
                    ->searchable(),
                TextColumn::make('last_seen_at')
                    ->dateTime()
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
