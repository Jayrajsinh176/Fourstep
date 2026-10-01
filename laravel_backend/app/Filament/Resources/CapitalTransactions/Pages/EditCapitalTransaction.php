<?php

namespace App\Filament\Resources\CapitalTransactions\Pages;

use App\Filament\Resources\CapitalTransactions\CapitalTransactionResource;
use Filament\Resources\Pages\EditRecord;

class EditCapitalTransaction extends EditRecord
{
    protected static string $resource = CapitalTransactionResource::class;
}