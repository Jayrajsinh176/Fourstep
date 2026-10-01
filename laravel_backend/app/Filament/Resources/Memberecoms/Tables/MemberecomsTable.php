<?php

namespace App\Filament\Resources\Memberecoms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
 use Filament\Actions\ViewAction;
class MemberecomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
         ->columns([

    TextColumn::make('id')
        ->label('Member ID')
        ->sortable(),

    TextColumn::make('fullname')
        ->label('Full Name')
        ->searchable()
        ->sortable(),

    TextColumn::make('mobile_no')
        ->label('Mobile')
        ->searchable(),

    TextColumn::make('email')
        ->label('Email')
        ->searchable(),

    TextColumn::make('gender')
        ->label('Gender'),

    TextColumn::make('dob')
        ->label('Date of Birth')
        ->date(),

   TextColumn::make('address')
    ->label('Address')
    ->formatStateUsing(fn ($record) =>
        $record->address . ', ' .
        $record->district . ', ' .
        $record->city . ', ' .
        $record->state . ' - ' .
        $record->pin_code
    )
    ->wrap(),
    

    TextColumn::make('created_at')
        ->label('Joined On')
        ->dateTime()
        ->sortable(),

])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
