<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            DatePicker::make('expense_date')
                ->label('Expense Date')
                ->required()
                ->default(now()),

TextInput::make('expense_type')
    ->label('Expense Type')
    ->placeholder('e.g. Salary, Transport, Office Rent')
    ->maxLength(100)
    ->required(),

            TextInput::make('amount')
                ->label('Amount')
                ->numeric()
                ->prefix('₹')
                ->required(),

            Select::make('payment_mode')
                ->label('Payment Mode')
                ->options([
                    'Cash' => 'Cash',
                    'Bank' => 'Bank',
                    'UPI' => 'UPI',
                ])
                ->default('Bank')
                ->required(),

            Textarea::make('remarks')
                ->label('Remarks')
                ->rows(3)
                ->columnSpanFull(),

        ])->columns(2);
    }
}