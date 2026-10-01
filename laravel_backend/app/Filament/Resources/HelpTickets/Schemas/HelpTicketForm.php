<?php

namespace App\Filament\Resources\HelpTickets\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;



class HelpTicketForm
{
  public static function form(Schema $schema): schema
{
    return $schema->schema([



  TextInput::make('member_name')
        ->label('Member Name')
        ->disabled()
        ->dehydrated(false)
        ->formatStateUsing(fn ($record) => $record->member->fullname ?? '-'),

TextInput::make('member_email')
        ->label('Email')
        ->disabled()
        ->dehydrated(false)
        ->formatStateUsing(fn ($record) => $record->member->email ?? '-'),

    TextInput::make('member_mobile')
        ->label('Mobile')
        ->disabled()
        ->dehydrated(false)
        ->formatStateUsing(fn ($record) => $record->member->mobile_no ?? '-'),

    TextInput::make('category')
        ->disabled(),

    TextInput::make('subject')
        ->disabled(),

    Textarea::make('details')
        ->disabled(),

    // 🔥 AUTO STATUS CHANGE
    Textarea::make('admin_reply')
        ->label('Reply')
        ->live()
        ->afterStateUpdated(function ($state, callable $set) {
            if (!empty($state)) {
                $set('status', 'replied');
            } else {
                $set('status', 'pending');
            }
        }),

    Select::make('status')
        ->options([
            'pending' => 'Pending',
            'replied' => 'Replied',
        ])
        ->default('pending'),
    ]);
}
}
