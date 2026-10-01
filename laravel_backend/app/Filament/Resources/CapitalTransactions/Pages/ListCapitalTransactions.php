<?php

namespace App\Filament\Resources\CapitalTransactions\Pages;

use App\Filament\Resources\CapitalTransactions\CapitalTransactionResource;
use App\Filament\Resources\CapitalTransactions\Widgets\CapitalTransactionStats;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCapitalTransactions extends ListRecords
{
    protected static string $resource = CapitalTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Capital Transaction'),
                ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CapitalTransactionStats::class,
        ];
    }
}