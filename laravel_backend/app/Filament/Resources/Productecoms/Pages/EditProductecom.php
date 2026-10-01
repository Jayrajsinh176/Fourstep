<?php

namespace App\Filament\Resources\Productecoms\Pages;

use App\Filament\Resources\Productecoms\ProductecomResource;
use App\Services\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductecom extends EditRecord
{
    protected static string $resource = ProductecomResource::class;

    protected function afterSave(): void
    {
        ActivityLogService::log(
            'Ecommerce Panel',
            'Product Updated',
            'Updated product "' . $this->record->name . '"'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function () {
                    ActivityLogService::log(
                        'Ecommerce Panel',
                        'Product Deleted',
                        'Deleted product "' . $this->record->name . '"'
                    );
                }),
        ];
    }
}