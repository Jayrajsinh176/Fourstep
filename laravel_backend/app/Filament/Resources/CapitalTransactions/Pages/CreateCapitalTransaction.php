<?php

namespace App\Filament\Resources\CapitalTransactions\Pages;

use App\Filament\Resources\CapitalTransactions\CapitalTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCapitalTransaction extends CreateRecord
{
    protected static string $resource = CapitalTransactionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}