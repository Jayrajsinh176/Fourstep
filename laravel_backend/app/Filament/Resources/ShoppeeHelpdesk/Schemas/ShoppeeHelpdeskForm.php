<?php

namespace App\Filament\Resources\ShoppeeHelpdesk\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ShoppeeHelpdeskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('user_id')
                    ->disabled(),

                TextInput::make('subject')
                    ->disabled(),

                Textarea::make('message')
                    ->rows(5)
                    ->disabled(),

                Textarea::make('admin_reply')
                    ->label('Admin Reply')
                    ->rows(5)
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {

                        if ($state) {
                            $set('status', 'open');
                        }

                    }),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'open' => 'Open',
                        'closed' => 'Closed',
                    ])
                    ->required(),

            ]);
    }
}