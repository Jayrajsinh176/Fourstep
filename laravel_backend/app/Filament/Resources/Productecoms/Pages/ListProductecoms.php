<?php

namespace App\Filament\Resources\Productecoms\Pages;

use App\Filament\Resources\Productecoms\ProductecomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductecoms extends ListRecords
{
    protected static string $resource = ProductecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
