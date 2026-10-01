<?php

namespace App\Filament\Resources\Categoryecoms\Pages;

use App\Filament\Resources\Categoryecoms\CategoryecomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoryecoms extends ListRecords
{
    protected static string $resource = CategoryecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
