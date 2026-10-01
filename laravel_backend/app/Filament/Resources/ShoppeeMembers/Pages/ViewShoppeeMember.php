<?php

namespace App\Filament\Resources\ShoppeeMembers\Pages;

use App\Filament\Resources\ShoppeeMembers\ShoppeeMemberResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShoppeeMember extends ViewRecord
{
    protected static string $resource = ShoppeeMemberResource::class;

    protected function getHeaderActions(): array
    {
        return[];
    }
}
