<?php

namespace App\Filament\Resources\ShoppeeProductRequests\Pages;

use App\Filament\Resources\ShoppeeProductRequests\ShoppeeProductRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewShoppeeProductRequest extends ViewRecord
{
    protected static string $resource = ShoppeeProductRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [ ];
    }
}
