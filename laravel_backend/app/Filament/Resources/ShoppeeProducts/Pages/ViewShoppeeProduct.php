<?php

namespace App\Filament\Resources\ShoppeeProducts\Pages;

use App\Filament\Resources\ShoppeeProducts\ShoppeeProductResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShoppeeProduct extends ViewRecord
{
    protected static string $resource = ShoppeeProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
