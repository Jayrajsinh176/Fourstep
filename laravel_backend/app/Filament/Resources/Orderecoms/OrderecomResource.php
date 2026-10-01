<?php

namespace App\Filament\Resources\Orderecoms;

use App\Filament\Resources\Orderecoms\Pages\CreateOrderecom;
use App\Filament\Resources\Orderecoms\Pages\EditOrderecom;
use App\Filament\Resources\Orderecoms\Pages\ListOrderecoms;
use App\Filament\Resources\Orderecoms\Schemas\OrderecomForm;
use App\Filament\Resources\Orderecoms\Tables\OrderecomsTable;
use App\Models\Orderecom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
class OrderecomResource extends Resource
{
    protected static ?string $model = Orderecom::class;

   protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Orders';
protected static ?string $modelLabel = 'Order';
protected static ?string $pluralModelLabel = 'Orders';  

protected static string | \UnitEnum | null $navigationGroup = 'Ecommerce Panel';
protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return OrderecomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrderecomsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNull('mlm_member_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderecoms::route('/'),
            'create' => CreateOrderecom::route('/create'),
            'edit' => EditOrderecom::route('/{record}/edit'),
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

    return $user->hasPermission('ecommerce_orders.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}

}
