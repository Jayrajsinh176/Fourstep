<?php

namespace App\Filament\Resources\Orderecoms\Pages;

use App\Filament\Resources\Orderecoms\OrderecomResource;
use App\Services\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderecom extends EditRecord
{
    protected static string $resource = OrderecomResource::class;

    protected string $oldStatus;

    protected function beforeFill(): void
    {
        $this->oldStatus = $this->record->status;
    }

    protected function afterSave(): void
    {
        if ($this->oldStatus !== $this->record->status) {

            ActivityLogService::log(
                'Ecommerce Panel',
                'Ecommerce Order Status Changed',
                'Order #' . $this->record->id .
                ' status changed from "' .
                ucfirst($this->oldStatus) .
                '" to "' .
                ucfirst($this->record->status) .
                '" for customer ' .
                ($this->record->customer_name ?? '-')
            );
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}