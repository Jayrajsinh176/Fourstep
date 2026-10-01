<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Activity Logs';

    protected static ?string $pluralModelLabel = 'Activity Logs';

    protected static ?string $modelLabel = 'Activity Log';

    protected static string | \UnitEnum | null $navigationGroup = 'User Access';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\ActivityLogs\Tables\ActivityLogsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
    
    public static function canAccess(): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    if ($user->isOwner()) {
        return true;
    }

    return $user->hasPermission('activity_logs.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
}