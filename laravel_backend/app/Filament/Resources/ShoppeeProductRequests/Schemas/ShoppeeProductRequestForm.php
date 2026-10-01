<?php

namespace App\Filament\Resources\ShoppeeProductRequests\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class ShoppeeProductRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('product_id')->disabled(),

                TextInput::make('quantity')->disabled(),

                TextInput::make('total_products')->disabled(),

                TextInput::make('total_amount')->disabled(),

                TextInput::make('total_pv')->disabled(),

                Select::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Cancelled' => 'Cancelled',
                    ])
                    ->required(),
            ]);
    }
}