<?php

namespace App\Filament\Resources\MemberOrder\Pages;

use App\Filament\Resources\MemberOrder\MemberOrderResource;
use App\Services\ActivityLogService;
use Filament\Resources\Pages\EditRecord;

class EditMemberOrder extends EditRecord
{
    protected static string $resource = MemberOrderResource::class;

    protected string $oldStatus;

    protected function beforeFill(): void
    {
        $this->oldStatus = $this->record->status;
    }

    protected function afterSave(): void
    {
        if ($this->oldStatus !== $this->record->status) {

            ActivityLogService::log(
                'Member Panel',
                'Member Order Status Changed',
                'Order #' . $this->record->id .
                ' status changed from "' .
                ucfirst($this->oldStatus) .
                '" to "' .
                ucfirst($this->record->status) .
                '" for member ' .
                $this->record->mlm_member_id
            );
        }
    }
}