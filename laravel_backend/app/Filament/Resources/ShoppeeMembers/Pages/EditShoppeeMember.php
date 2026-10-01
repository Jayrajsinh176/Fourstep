<?php

namespace App\Filament\Resources\ShoppeeMembers\Pages;

use App\Filament\Resources\ShoppeeMembers\ShoppeeMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShoppeeMember extends EditRecord
{
    protected static string $resource = ShoppeeMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
