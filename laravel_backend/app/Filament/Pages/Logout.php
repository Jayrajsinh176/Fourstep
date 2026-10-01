<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Logout extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-left-on-rectangle';

    protected static ?string $navigationLabel = 'Logout';

    protected static ?string $slug = 'logout';

    protected static ?int $navigationSort = 999; // bottom

    public function mount(): void
    {
        Auth::logout();
        redirect('/admin/login');
    }

    protected static bool $shouldRegisterNavigation = true;
}