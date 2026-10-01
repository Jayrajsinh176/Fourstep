<?php

namespace App\Filament\Resources\AdminUsers\Pages;

use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Services\ActivityLogService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditAdminUser extends EditRecord
{
    protected static string $resource = AdminUserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        ActivityLogService::log(
            'User Access',
            'Admin Updated',
            'Updated admin user: ' . $this->record->name . ' (' . $this->record->email . ')'
        );

        Notification::make()
            ->title('Admin User Updated Successfully')
            ->success()
            ->send();
    }
}