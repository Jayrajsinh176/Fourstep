<?php

namespace App\Filament\Resources\ShoppeeBalanceRequests;

use App\Filament\Resources\ShoppeeBalanceRequests\Pages\EditShoppeeBalanceRequest;
use App\Filament\Resources\ShoppeeBalanceRequests\Pages\ListShoppeeBalanceRequests;
use App\Models\Shoppee_BalanceRequest;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ShoppeeBalanceRequestResource extends Resource
{
    protected static ?string $model = Shoppee_BalanceRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    // ✅ Navigation Settings
    protected static string|UnitEnum|null $navigationGroup = 'Shoppee Panel';
    protected static ?string $navigationLabel = 'Balance Requests';
    protected static ?string $pluralLabel = 'Balance Requests';
    protected static ?int $navigationSort = 5;

    // ❌ Disable Create Button
    public static function canCreate(): bool
    {
        return false;
    }

    // ✅ FORM (View / Edit)
    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\ShoppeeBalanceRequests\Schemas\ShoppeeBalanceRequestForm::configure($schema);
    }

    // ✅ TABLE
    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\ShoppeeBalanceRequests\Tables\ShoppeeBalanceRequestsTable::configure($table);
    }

    // ✅ PAGES
    public static function getPages(): array
    {
        return [
            'index' => ListShoppeeBalanceRequests::route('/'),
            'edit' => EditShoppeeBalanceRequest::route('/{record}/edit'),
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

    return $user->hasPermission('shoppee_balance_requests.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
    
}