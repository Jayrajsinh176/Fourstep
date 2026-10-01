<?php

namespace App\Filament\Resources\ShoppeeBalanceRequests\Pages;

use App\Filament\Resources\ShoppeeBalanceRequests\ShoppeeBalanceRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppeeBalanceRequests extends ListRecords
{
    protected static string $resource = ShoppeeBalanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
