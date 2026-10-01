<?php

namespace App\Filament\Resources\Categoryecoms\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryecomForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('image')
                    ->image()
                    ->directory('images/categories')
                    ->imagePreviewHeight('100')
                    ->preserveFilenames()
                    ->nullable(),

            ]);
    }
}