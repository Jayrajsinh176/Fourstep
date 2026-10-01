<?php

namespace App\Filament\Resources\ShoppeeProducts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use App\Models\Shoppee_Product;


class ShoppeeProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // 🔽 Category Dropdown
                Select::make('category')
                    ->options(
                        Shoppee_Product::pluck('category', 'category')->toArray()
                    )
                    ->searchable()
                    ->required(),

                TextInput::make('productname')
                    ->label('Product Name')
                    ->required(),

                TextInput::make('pv')
                    ->numeric()
                    ->required(),

                TextInput::make('mrp')
                    ->numeric()
                    ->required(),

                TextInput::make('offerprice')
                    ->numeric()
                    ->required(),
                 TextInput::make('minimum_quantity')
    ->label('Minimum Quantity')
    ->numeric()
    ->default(1)
    ->required(),

                TextInput::make('commission')
                    ->numeric()
                    ->required(),
            ]);
    }
}