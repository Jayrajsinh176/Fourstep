<?php

namespace App\Filament\Resources\Memberecoms\Pages;

use App\Filament\Resources\Memberecoms\MemberecomResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberecom extends EditRecord
{
    protected static string $resource = MemberecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
