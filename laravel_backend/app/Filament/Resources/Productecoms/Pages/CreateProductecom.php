<?php

namespace App\Filament\Resources\Productecoms\Pages;

use App\Filament\Resources\Productecoms\ProductecomResource;
use App\Services\ActivityLogService;
use Filament\Resources\Pages\CreateRecord;

class CreateProductecom extends CreateRecord
{
    protected static string $resource = ProductecomResource::class;

    protected function afterCreate(): void
    {
        ActivityLogService::log(
            'Ecommerce Panel',
            'Product Created',
            'Created product "' .
            $this->record->name .
            '"'
        );
    }
}