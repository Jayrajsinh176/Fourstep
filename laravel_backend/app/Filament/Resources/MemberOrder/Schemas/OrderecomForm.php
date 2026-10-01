<?php

namespace App\Filament\Resources\MemberOrder\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;

class MemberOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // MEMBER
 Placeholder::make('member_name')
    ->label('Member Name')
    ->content(fn ($record) => $record?->mlmMember?->fullname ?? '-'),
                // MLM ID
            Placeholder::make('mlm_member_id')
    ->label('MLM ID')
    ->content(fn ($record) => $record->mlm_member_id ?? '-'),

                // MEMBER ADDRESS
              Placeholder::make('member_address')
    ->label('Member Address')
    ->content(fn ($record) => $record?->mlmMember?->address ?? 'No Address'),

                // DELIVERY
           Placeholder::make('delivery_name')
    ->label('Delivery Name')
    ->content(fn ($record) => $record->delivery_name ?? '-'),
      
       Placeholder::make('delivery_address')
    ->label('Delivery Address')
    ->content(fn ($record) => $record->delivery_address ?? '-'),
                // PRODUCT
                    Placeholder::make('product_name')
    ->label('Product')
    ->content(function ($record) {
        return $record->items
            ->pluck('product.name')
            ->filter()
            ->implode(', ');
    }),
             Placeholder::make('quantity')
    ->label('Quantity')
    ->content(fn ($record) => $record->quantity),

                // PAYMENT
          Placeholder::make('payment_method')
    ->label('Payment Method')
    ->content(fn ($record) => strtoupper($record->payment_method ?? '-')),
    
                // WALLET
             Placeholder::make('wallet_type')
    ->label('Wallet Type')
    ->content(fn ($record) => $record->wallet_type ?? '-'),

            Placeholder::make('wallet_type')
    ->label('Wallet Type')
    ->content(fn ($record) => $record->wallet_type ?? '-'),
                
              Placeholder::make('wallet_amount')
    ->label('Wallet Amount')
    ->content(fn ($record) => '₹ ' . number_format($record->wallet_amount ?? 0, 2)),

                // COUPON
              Placeholder::make('coupon_code')
    ->label('Coupon Code')
    ->content(fn ($record) => $record->coupon_code ?? '-'),
    
            Placeholder::make('coupon_discount')
    ->label('Coupon Discount')
    ->content(fn ($record) => '₹ ' . number_format($record->coupon_discount ?? 0, 2)),
    

                // AMOUNTS
             Placeholder::make('amount_paid')
    ->label('Amount Paid')
    ->content(fn ($record) => '₹ ' . number_format($record->amount_paid ?? 0, 2)),
    
              Placeholder::make('total_amount')
    ->label('Total Amount')
    ->content(fn ($record) => '₹ ' . number_format($record->total_amount ?? 0, 2)),
    

                // STATUS
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