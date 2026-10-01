<?php

namespace App\Filament\Resources\ShoppeeProducts\Pages;

use App\Filament\Resources\ShoppeeProducts\ShoppeeProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppeeProducts extends ListRecords
{
    protected static string $resource = ShoppeeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
