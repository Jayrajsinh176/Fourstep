<?php

namespace App\Filament\Resources\Memberecoms;

use App\Filament\Resources\Memberecoms\Pages\CreateMemberecom;
use App\Filament\Resources\Memberecoms\Pages\EditMemberecom;
use App\Filament\Resources\Memberecoms\Pages\ListMemberecoms;
use App\Filament\Resources\Memberecoms\Schemas\MemberecomForm;
use App\Filament\Resources\Memberecoms\Tables\MemberecomsTable;
use App\Models\Memberecom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemberecomResource extends Resource
{
    protected static ?string $model = Memberecom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

protected static ?string $navigationLabel = 'Customers';
protected static ?string $pluralModelLabel = 'Customers';
protected static ?string $modelLabel = 'Customer';

protected static string | \UnitEnum | null $navigationGroup = 'Ecommerce Panel';
protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return MemberecomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberecomsTable::configure($table);
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
            'index' => ListMemberecoms::route('/'),
            'create' => CreateMemberecom::route('/create'),
            'edit' => EditMemberecom::route('/{record}/edit'),
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

    return $user->hasPermission('customers');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
}
