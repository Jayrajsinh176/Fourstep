<?php

namespace App\Filament\Resources\AdminUsers\Pages;

use App\Filament\Resources\AdminUsers\AdminUserResource;
use App\Mail\AdminWelcomeMail;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Throwable;
use App\Services\ActivityLogService;

class CreateAdminUser extends CreateRecord
{
    protected static string $resource = AdminUserResource::class;

    protected string $plainPassword;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Save plain password for email
        $this->plainPassword = $data['password'];

        // Hash password before saving
        $data['password'] = Hash::make($data['password']);

        return $data;
    }

    protected function afterCreate(): void
{
    try {

        Mail::to($this->record->email)
            ->send(new AdminWelcomeMail(
                $this->record->name,
                $this->record->email,
                $this->plainPassword
            ));

        Notification::make()
            ->title('Admin User Created Successfully')
            ->body('Login credentials have been emailed successfully.')
            ->success()
            ->send();

    } catch (Throwable $e) {

        report($e);

        Notification::make()
            ->title('Admin User Created')
            ->body('The user was created successfully, but the email could not be sent.')
            ->warning()
            ->send();
    }
    ActivityLogService::log(
    'User Access',
    'Admin Created',
    'Created admin user: ' . $this->record->name . ' (' . $this->record->email . ')'
);
}
}