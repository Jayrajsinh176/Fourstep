<?php

namespace App\Filament\Resources\ShoppeeKycResource\Pages;

use App\Filament\Resources\ShoppeeKycResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShoppeeKyc extends EditRecord
{
    protected static string $resource = ShoppeeKycResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}