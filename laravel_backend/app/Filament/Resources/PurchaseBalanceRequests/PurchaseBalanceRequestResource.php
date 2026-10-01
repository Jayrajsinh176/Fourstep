<?php

namespace App\Filament\Resources\PurchaseBalanceRequests;

use App\Models\PurchaseBalanceRequest;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables;
use BackedEnum;
use UnitEnum;

use App\Filament\Resources\PurchaseBalanceRequests\Pages\ListPurchaseBalanceRequests;
use App\Filament\Resources\PurchaseBalanceRequests\Tables\PurchaseBalanceRequestsTable;

class PurchaseBalanceRequestResource extends Resource
{
    protected static ?string $model = PurchaseBalanceRequest::class;

    protected static ?string $navigationLabel = 'Balance Requests';

    protected static string|UnitEnum|null $navigationGroup = 'Member Panel';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return PurchaseBalanceRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseBalanceRequests::route('/'),
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

    return $user->hasPermission('member_balance_requests.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
    
}