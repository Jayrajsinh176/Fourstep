<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MemberTree extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShare;

    protected static string|\UnitEnum|null $navigationGroup = 'Member Panel';

    protected static ?string $navigationLabel = 'View Tree';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.member-tree';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        return $user->hasPermission('view_tree');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}