<?php

namespace App\Filament\Resources\MemberKycs\Pages;
use App\Filament\Resources\MemberKycs\MemberKycResource;
use Filament\Resources\Pages\ListRecords;

class ListMemberKycs extends ListRecords
{
    protected static string $resource = MemberKycResource::class;

    protected function getHeaderActions(): array
    {
        return []; // ❌ disable create
    }
}