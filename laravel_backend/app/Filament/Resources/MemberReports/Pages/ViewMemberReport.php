<?php

namespace App\Filament\Resources\MemberReports\Pages;

use App\Filament\Resources\MemberReports\MemberReportResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\EditAction;

class ViewMemberReport extends ViewRecord
{
    protected static string $resource = MemberReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}