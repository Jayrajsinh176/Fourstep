<?php

namespace App\Filament\Resources\ShoppeeProducts\Pages;

use App\Filament\Resources\ShoppeeProducts\ShoppeeProductResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditShoppeeProduct extends EditRecord
{
    protected static string $resource = ShoppeeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
