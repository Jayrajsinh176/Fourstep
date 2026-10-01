<?php

namespace App\Filament\Resources\ShoppeeProductRequests\Pages;

use App\Filament\Resources\ShoppeeProductRequests\ShoppeeProductRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShoppeeProductRequest extends EditRecord
{
    protected static string $resource = ShoppeeProductRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
