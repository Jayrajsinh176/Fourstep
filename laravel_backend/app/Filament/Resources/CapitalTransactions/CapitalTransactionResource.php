<?php

namespace App\Filament\Resources\CapitalTransactions;

use App\Filament\Resources\CapitalTransactions\Pages\CreateCapitalTransaction;
use App\Filament\Resources\CapitalTransactions\Pages\EditCapitalTransaction;
use App\Filament\Resources\CapitalTransactions\Pages\ListCapitalTransactions;
use App\Filament\Resources\CapitalTransactions\Schemas\CapitalTransactionForm;
use App\Filament\Resources\CapitalTransactions\Tables\CapitalTransactionsTable;
use App\Models\CapitalTransaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CapitalTransactionResource extends Resource
{
    protected static ?string $model = CapitalTransaction::class;

protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationLabel = 'Capital Management';

    protected static ?string $modelLabel = 'Capital Transaction';

    protected static ?string $pluralModelLabel = 'Capital Transactions';

    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';

    public static function form(Schema $schema): Schema
    {
        return CapitalTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CapitalTransactionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCapitalTransactions::route('/'),
            'create' => CreateCapitalTransaction::route('/create'),
            'edit' => EditCapitalTransaction::route('/{record}/edit'),
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

    return $user->hasPermission('capital_management');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
}