<?php

namespace App\Filament\Resources\Categoryecoms\Pages;

use App\Filament\Resources\Categoryecoms\CategoryecomResource;
use App\Services\ActivityLogService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoryecom extends EditRecord
{
    protected static string $resource = CategoryecomResource::class;

    protected function afterSave(): void
    {
        ActivityLogService::log(
            'Ecommerce Panel',
            'Category Updated',
            'Updated category "' . $this->record->name . '"'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function () {
                    ActivityLogService::log(
                        'Ecommerce Panel',
                        'Category Deleted',
                        'Deleted category "' . $this->record->name . '"'
                    );
                }),
        ];
    }
}