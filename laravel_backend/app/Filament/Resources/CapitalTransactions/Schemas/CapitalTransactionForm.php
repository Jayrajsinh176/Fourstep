<?php

namespace App\Filament\Resources\CapitalTransactions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CapitalTransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('transaction_date')
                    ->label('Transaction Date')
                    ->required()
                    ->default(now())
                    ->native(false),

                Select::make('transaction_type')
                    ->label('Transaction Type')
                    ->options([
                        'credit' => 'Credit',
                        'debit' => 'Debit',
                    ])
                    ->required()
                    ->native(false),

                Select::make('category')
                    ->label('Category')
                    ->options([
                        'normal' => 'Normal',
                        'capital' => 'Capital',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('amount')
                    ->label('Amount')
                    ->numeric()
                    ->prefix('₹')
                    ->required()
                    ->minValue(0.01),

                Textarea::make('remarks')
                    ->label('Remarks')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]);
    }
}