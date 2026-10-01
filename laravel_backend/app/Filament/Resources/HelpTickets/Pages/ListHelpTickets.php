<?php

namespace App\Filament\Resources\HelpTickets\Pages;

use App\Filament\Resources\HelpTickets\HelpTicketResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHelpTickets extends ListRecords
{
    protected static string $resource = HelpTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }
}
