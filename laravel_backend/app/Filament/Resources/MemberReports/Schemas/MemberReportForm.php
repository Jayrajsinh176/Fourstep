<?php

namespace App\Filament\Resources\MemberReports\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Illuminate\Validation\Rules\Unique;

class MemberReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('fullname')
                    ->required()
                    ->maxLength(255),

      TextInput::make('mobile_no')
    ->numeric()
    ->required()
    ->length(10)
    ->unique(ignoreRecord: true),
    
                TextInput::make('email')
                    ->email(),

                DatePicker::make('dob'),

                Select::make('gender')
                    ->options([
                        'Male' => 'Male',
                        'Female' => 'Female',
                        'Other' => 'Other',
                    ]),

                TextInput::make('pan_number'),

                Textarea::make('address')
                    ->columnSpanFull(),

                TextInput::make('city'),

                TextInput::make('district'),

                TextInput::make('state'),

                TextInput::make('pin_code'),

                Textarea::make('shipping_address')
                    ->columnSpanFull(),

                TextInput::make('shipping_city'),

                TextInput::make('shipping_district'),

                TextInput::make('shipping_state'),

                TextInput::make('shipping_pin_code'),

                TextInput::make('nominee_name'),

                TextInput::make('nominee_relation'),

                TextInput::make('nominee_mobile_no'),

                Textarea::make('nominee_address')
                    ->columnSpanFull(),

                TextInput::make('nominee_city'),

                TextInput::make('nominee_district'),

                TextInput::make('nominee_state'),

                TextInput::make('nominee_pin_code'),

            ]);
    }
}