<?php

namespace App\Filament\Resources\Expenses\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->defaultSort('expense_date', 'desc')

            ->columns([

                TextColumn::make('expense_date')
                    ->label('Expense Date')
                    ->date('d-m-Y')
                    ->sortable(),

                TextColumn::make('expense_type')
                    ->label('Expense Type')
                    ->searchable()
                    ->badge(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('INR')
                    ->sortable(),

                TextColumn::make('payment_mode')
                    ->label('Payment Mode')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Cash' => 'success',
                        'Bank' => 'primary',
                        'UPI' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(40),

                TextColumn::make('user.name')
                    ->label('Created By')
                    ->placeholder('-'),

            ])

            ->searchable()

            ->paginated([10, 25, 50]);
    }
}