<?php

namespace App\Filament\Resources\Orderecoms\Schemas;

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

                // ✅ MEMBER
            Select::make('member_id')
    ->label('Member Name')
    ->relationship('mlmMember', 'fullname')
    ->disabled()
    ->dehydrated(false),

                // ✅ MLM ID
                TextInput::make('mlm_member_id')
                    ->label('MLM ID')
                    ->disabled()
                    ->dehydrated(false),

                // ✅ MEMBER ADDRESS
            Placeholder::make('member_address')
    ->label('Member Address')
    ->content(fn ($record) => $record?->mlmMember?->address ?? 'No Address'),

                // ✅ DELIVERY
                TextInput::make('delivery_name')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('delivery_address')
                    ->disabled()
                    ->dehydrated(false),

                // ✅ PRODUCT
                TextInput::make('product_name')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('quantity')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),

                // ✅ PAYMENT
                TextInput::make('payment_method')
                    ->disabled()
                    ->dehydrated(false),

                // ✅ WALLET (FIXED)
                Placeholder::make('wallet_used')
                    ->label('Wallet Used')
                    ->content(fn ($record) => $record->wallet_used ? 'Yes' : 'No'),

                TextInput::make('wallet_type')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('wallet_amount')
                    ->disabled()
                    ->dehydrated(false),

                // ✅ COUPON
                TextInput::make('coupon_code')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('coupon_discount')
                    ->disabled()
                    ->dehydrated(false),

                // ✅ AMOUNTS
                TextInput::make('amount_paid')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('total_amount')
                    ->disabled()
                    ->dehydrated(false),

                // ✅ IMAGE
                FileUpload::make('image')
                    ->image()
                    ->disk('public')
                    ->directory('product')
                    ->visibility('public')
                    ->disabled()
                    ->dehydrated(false),

                // 🔥 STATUS (ONLY EDITABLE)
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