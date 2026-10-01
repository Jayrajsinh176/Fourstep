<?php

namespace App\Filament\Resources\HelpTickets\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HelpTicketsTable
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('member.fullname')
                    ->label('Name')
                    ->default('N/A'),

                TextColumn::make('member.email')
                    ->label('Email')
                    ->default('N/A'),

                TextColumn::make('member.mobile_no')
                    ->label('Mobile')
                    ->default('N/A'),

                TextColumn::make('member_type')
                    ->label('Member Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'mlm'
                        ? 'MLM'
                        : 'E-commerce')
                    ->colors([
                        'success' => 'mlm',
                        'info' => 'ecommerce',
                    ]),

                // ImageColumn::make('image')
                //     ->label('Attachment')
                //     ->getStateUsing(fn ($record) => $record->image ? asset($record->image) : null)
                //     ->height(70)
                //     ->width(70)
                //     ->square()
                //     ->url(fn ($record) => $record->image ? asset($record->image) : null)
                //     ->openUrlInNewTab(),

                TextColumn::make('category'),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'replied',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime(),

            ])
            ->actions([
                EditAction::make(),
            ]);
    }
}