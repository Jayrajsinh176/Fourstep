<?php

namespace App\Filament\Resources\ShoppeeMembers;


use App\Filament\Resources\ShoppeeMembers\Pages\ListShoppeeMembers;
use App\Filament\Resources\ShoppeeMembers\Pages\ViewShoppeeMember;
use App\Filament\Resources\ShoppeeMembers\Schemas\ShoppeeMemberForm;
use App\Filament\Resources\ShoppeeMembers\Schemas\ShoppeeMemberInfolist;
use App\Filament\Resources\ShoppeeMembers\Tables\ShoppeeMembersTable;
use App\Models\Shoppee_Member;
use App\Filament\Resources\ShoppeeMembers\Pages\EditShoppeeMember;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoppeeMemberResource extends Resource
{
    protected static ?string $model = Shoppee_Member::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

protected static string | \UnitEnum | null $navigationGroup = 'Shoppee Panel';

protected static ?string $navigationLabel = 'Branch List';
protected static ?string $pluralLabel = 'Members';
protected static ?string $modelLabel = 'Members';

protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ShoppeeMemberForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShoppeeMemberInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoppeeMembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoppeeMembers::route('/'),
            
            'view' => ViewShoppeeMember::route('/{record}'),
              'edit' => EditShoppeeMember::route('/{record}/edit'),
           
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

    return $user->hasPermission('branch_list.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
}
