<?php

namespace App\Filament\Resources\ShoppeeProducts\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class ShoppeeProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('productname')
                    ->label('Product Name')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('pv')
                    ->label('PV'),

                Tables\Columns\TextColumn::make('mrp')
                    ->money('INR'),

                Tables\Columns\TextColumn::make('offerprice')
                    ->money('INR')
                    ->color('success'),
                    Tables\Columns\TextColumn::make('minimum_quantity')
    ->label('Min Qty')
    ->badge()
    ->color('primary'),

                Tables\Columns\TextColumn::make('commission')
                    ->color('warning'),

                // 🔥 PROFIT CALCULATION
                Tables\Columns\TextColumn::make('profit')
                    ->label('Profit')
                    ->getStateUsing(fn ($record) => $record->offerprice - $record->commission)
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),

            ])

            ->filters([
                //
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}