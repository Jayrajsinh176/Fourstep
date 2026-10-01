<?php

namespace App\Filament\Resources\ShoppeeMembers\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;

class ShoppeeMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('member_id')
                    ->label('Member ID')
                    ->disabled(),

                TextInput::make('fullname')
                    ->required(),
                    TextInput::make('user_pan')
    ->label('User PAN'),

TextInput::make('aadhaar_no')
    ->label('Aadhaar No'),

Textarea::make('user_address')
    ->label('User Address'),

               TextInput::make('branch_name')
                    ->required(),
                    
                    
Select::make('branch_type')
    ->label('Branch Type')
    ->options([
        'Mega Branch' => 'Mega Branch',
        'Mini Branch' => 'Mini Branch',
        'Area Branch' => 'Area Branch',
    ])
    ->required(),

                TextInput::make('branch_pan'),

                DatePicker::make('dob')
                    ->required(),

                TextInput::make('gst_no'),

                TextInput::make('email')
                    ->email(),

                TextInput::make('mobile_no'),

                Textarea::make('address'),

                TextInput::make('pin_code'),

              TextInput::make('state'),
           

                TextInput::make('city'),

                TextInput::make('district'),
            ]);
    }
}