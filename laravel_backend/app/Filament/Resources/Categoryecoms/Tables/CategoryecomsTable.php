<?php

namespace App\Filament\Resources\Categoryecoms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoryecomsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                ImageColumn::make('image')
                    ->label('Category Image')
                    ->url(fn ($record) => asset('images/categories/' . $record->image), true)
                    ->height(120)
                    ->width(220)
                    ->extraImgAttributes([
                        'style' => '
                            object-fit: cover;
                            border-radius: 14px;
                            border: 1px solid #e5e7eb;
                            padding: 3px;
                            background: white;
                        ',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime('d M Y h:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->striped()

            ->recordActions([
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}