<?php

namespace App\Filament\Resources\Memberecoms\Pages;

use App\Filament\Resources\Memberecoms\MemberecomResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberecoms extends ListRecords
{
    protected static string $resource = MemberecomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
