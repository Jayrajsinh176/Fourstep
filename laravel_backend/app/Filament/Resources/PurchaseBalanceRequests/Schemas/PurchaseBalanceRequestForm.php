<?php

namespace App\Filament\Resources\PurchaseBalanceRequests\Schemas;

use Filament\Forms;

class PurchaseBalanceRequestForm
{
    public static function configure($schema)
    {
        return $schema->components([
            Forms\Components\TextInput::make('user_id')
                ->label('User ID')
                ->required(),

            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ])
                ->default('pending')
                ->required(),

            Forms\Components\Textarea::make('remarks')
                ->rows(3),
        ]);
    }
}