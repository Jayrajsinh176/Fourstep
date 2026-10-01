<?php

namespace App\Filament\Resources\MemberOrder;

use App\Filament\Resources\MemberOrder\Pages\CreateMemberOrder;
use App\Filament\Resources\MemberOrder\Pages\EditMemberOrder;
use App\Filament\Resources\MemberOrder\Pages\ListMemberOrders;
use App\Filament\Resources\MemberOrder\Schemas\MemberOrderForm;
use App\Filament\Resources\MemberOrder\Tables\MemberOrderTable;
use App\Models\Orderecom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberOrderResource extends Resource
{
    protected static ?string $model = Orderecom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Member Orders';
    protected static ?string $modelLabel = 'Member Order';
    protected static ?string $pluralModelLabel = 'Member Orders';

    // ✅ MOVE TO MEMBER PANEL
    protected static string | \UnitEnum | null $navigationGroup = 'Member Panel';

    protected static ?int $navigationSort = 9;

    // ✅ ONLY MEMBER ORDERS
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with([
            'mlmMember',
            'ecomMember',
            'items.product',
        ])
        ->whereNotNull('mlm_member_id');
}

    public static function form(Schema $schema): Schema
    {
        return MemberOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberOrderTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberOrders::route('/'),
            'edit' => EditMemberOrder::route('/{record}/edit'),
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

    return $user->hasPermission('member_orders.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
    
}