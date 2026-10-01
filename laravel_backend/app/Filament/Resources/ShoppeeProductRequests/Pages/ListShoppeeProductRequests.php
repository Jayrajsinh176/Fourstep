<?php

namespace App\Filament\Resources\ShoppeeProductRequests\Pages;

use App\Filament\Resources\ShoppeeProductRequests\ShoppeeProductRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppeeProductRequests extends ListRecords
{
    protected static string $resource = ShoppeeProductRequestResource::class;

   protected function getHeaderActions(): array
{
    return []; // ❌ no create button
}
}
