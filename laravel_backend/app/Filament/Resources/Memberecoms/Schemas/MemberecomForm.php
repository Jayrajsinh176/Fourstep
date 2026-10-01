<?php

namespace App\Filament\Resources\Memberecoms\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MemberecomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('fullname')
                    ->disabled()
                    ->dehydrated(false),

                DatePicker::make('dob')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('gender')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('email')
                    ->label('Email address')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('mobile_no')
                    ->disabled()
                    ->dehydrated(false),

                Textarea::make('address')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),

                TextInput::make('pin_code')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('state')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('city')
                    ->disabled()
                    ->dehydrated(false),

                // 🔒 PASSWORD (HIDDEN OR MASKED)
                TextInput::make('password')
                    ->password()
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }
}