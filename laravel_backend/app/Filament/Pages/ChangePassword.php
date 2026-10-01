<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.change-password';

    protected static string|null $navigationLabel = 'Change Password';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 1;
protected static ?string $title = '';
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->columns(1)
            ->components([
              TextInput::make('current_password')
    ->label('Current Password')
    ->placeholder('Enter your current password')
    ->password()
    ->revealable()
    ->required(),

TextInput::make('new_password')
    ->label('New Password')
    ->placeholder('Enter a new password')
    ->password()
    ->revealable()
    ->required()
    ->minLength(8),

TextInput::make('new_password_confirmation')
    ->label('Confirm New Password')
    ->placeholder('Re-enter your new password')
    ->password()
    ->revealable()
    ->required()
    ->same('new_password'),
            ]);
    }

    public function changePassword(): void
    {
        $user = Auth::user();

        if (! Hash::check(
            $this->data['current_password'],
            $user->password
        )) {
            Notification::make()
                ->title('Current Password Incorrect')
                ->body('Please enter your current password correctly.')
                ->danger()
                ->send();

            return;
        }

        $user->update([
            'password' => Hash::make(
                $this->data['new_password']
            ),
        ]);

        Notification::make()
            ->title('Password Updated')
            ->body('Your password has been changed successfully.')
            ->success()
            ->send();

        $this->form->fill();
    }
}