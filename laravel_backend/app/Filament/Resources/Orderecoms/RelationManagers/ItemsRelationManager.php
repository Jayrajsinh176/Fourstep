<?php

namespace App\Filament\Resources\Orderecoms\RelationManagers;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;


class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    
   public function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\ImageColumn::make('image')
                ->disk('public'),

            Tables\Columns\TextColumn::make('product_name')
                ->label('Product')
                ->searchable(),

            Tables\Columns\TextColumn::make('quantity'),

            Tables\Columns\TextColumn::make('total_amount')
                ->label('Total'),

        ])
        ->filters([])
        ->headerActions([])
        ->recordActions([])
        ->toolbarActions([]);
}
}
