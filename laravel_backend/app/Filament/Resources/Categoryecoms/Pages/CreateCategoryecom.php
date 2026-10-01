<?php

namespace App\Filament\Resources\Categoryecoms\Pages;

use App\Filament\Resources\Categoryecoms\CategoryecomResource;
use App\Services\ActivityLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryecom extends CreateRecord
{
    protected static string $resource = CategoryecomResource::class;

    protected function afterCreate(): void
    {
        ActivityLogService::log(
            'Ecommerce Panel',
            'Category Created',
            'Created category "' . $this->record->name . '"'
        );
    }
}