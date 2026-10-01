<?php

namespace App\Filament\Resources\Orderecoms\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;

class OrderecomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ✅ MEMBER (READ ONLY)
Placeholder::make('customer')
    ->label('Customer')
    ->content(fn ($record) => $record?->ecomMember?->fullname ?? '-'),
                // ✅ ADDRESS (VIEW ONLY)
         Placeholder::make('address')
    ->label('Address')
    ->content(fn ($record) => $record?->delivery_address ?? '-'),

                // ✅ PRODUCT NAME (READ ONLY)
          Placeholder::make('product_name')
    ->label('Product')
    ->content(function ($record) {
        return $record->items
            ->pluck('product.name')
            ->filter()
            ->implode(', ');
    }),

                // ✅ QUANTITY (READ ONLY)
             Placeholder::make('quantity')
    ->label('Quantity')
    ->content(fn ($record) => $record->quantity),

                // ✅ TOTAL AMOUNT (READ ONLY)
              Placeholder::make('total_amount')
    ->label('Total Amount')
    ->content(fn ($record) => '₹ ' . number_format($record->total_amount, 2)),

                // // ✅ IMAGE (VIEW ONLY - NO EDIT)
                // FileUpload::make('image')
                //     ->image()
                //     ->disk('public')
                //     ->directory('product')
                //     ->visibility('public')
                //     ->disabled()
                //     ->dehydrated(false),

                // 🔥 ONLY EDITABLE FIELD
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'dispatched' => 'Dispatched',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
        
                    ->required(),
                    
            ]);
    }
}