<?php

namespace App\Filament\Resources\ShoppeeBalanceRequests\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ShoppeeBalanceRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ✅ MEMBER NAME ADDED
                Forms\Components\TextInput::make('member.fullname')
                    ->label('Member Name')
                    ->disabled(),

                Forms\Components\TextInput::make('type')
                    ->disabled(),

                Forms\Components\TextInput::make('amount')
                    ->disabled(),

                Forms\Components\TextInput::make('mode_of_payment')
                    ->disabled(),

                Forms\Components\TextInput::make('transaction_no')
                    ->disabled(),

                Forms\Components\Placeholder::make('payment_slip_preview')
                    ->label('Payment Slip')
                    ->content(function ($record) {

                        if (!$record || !$record->payment_slip) {
                            return 'No Image';
                        }

                        $url = asset($record->payment_slip);

                        return new HtmlString(
                            '<a href="' . $url . '" target="_blank">
                                <img src="' . $url . '" 
                                     style="max-height:200px; border-radius:8px; cursor:pointer;" />
                             </a>'
                        );
                    }),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ])
                    ->required(),

            ]);
    }
}