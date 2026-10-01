<?php

namespace App\Filament\Resources\ShoppeeKyc\Pages;

use App\Filament\Resources\ShoppeeKyc\ShoppeeKycResource;
use Filament\Resources\Pages\ListRecords;

class ListShoppeeKyc extends ListRecords
{
    protected static string $resource = ShoppeeKycResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}