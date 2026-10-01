<?php

namespace App\Filament\Resources\ShoppeeMembers\Pages;

use App\Filament\Resources\ShoppeeMembers\ShoppeeMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListShoppeeMembers extends ListRecords
{
    protected static string $resource = ShoppeeMemberResource::class;

    protected function getHeaderActions(): array
    {
         return [];
    }
}
