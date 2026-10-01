<?php

namespace App\Filament\Resources\CapitalTransactions\Tables;

use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class CapitalTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date')
                    ->date('d-m-Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'credit' => 'success',
                        'debit' => 'danger',
                        default => 'gray',
                    }),
                    
                    Tables\Columns\TextColumn::make('category')
    ->label('Category')
    ->badge()
    ->formatStateUsing(fn (string $state): string => ucfirst($state))
    ->color(fn (string $state): string => match ($state) {
        'capital' => 'warning',
        'normal' => 'info',
        default => 'gray',
    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(40)
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('created_by')
                    ->label('Created By')
                    ->formatStateUsing(function ($state) {
                        return \App\Models\User::find($state)?->name ?? 'Admin';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d-m-Y h:i A')
                    ->sortable(),
            ])

            ->defaultSort('transaction_date', 'desc')

            ->filters([
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->label('Type')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ]),
            ])

            ->recordActions([
                EditAction::make(),
            ]);
    }
}