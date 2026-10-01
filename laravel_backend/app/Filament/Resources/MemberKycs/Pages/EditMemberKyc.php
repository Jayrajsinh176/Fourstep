<?php

namespace App\Filament\Resources\MemberKycs\Pages;

use App\Filament\Resources\MemberKycs\MemberKycResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberKyc extends EditRecord
{
    protected static string $resource = MemberKycResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
