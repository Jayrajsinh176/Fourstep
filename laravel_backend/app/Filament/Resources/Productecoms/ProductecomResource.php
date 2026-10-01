<?php

namespace App\Filament\Resources\Productecoms;

use App\Filament\Resources\Productecoms\Pages\CreateProductecom;
use App\Filament\Resources\Productecoms\Pages\EditProductecom;
use App\Filament\Resources\Productecoms\Pages\ListProductecoms;
use App\Filament\Resources\Productecoms\Schemas\ProductecomForm;
use App\Filament\Resources\Productecoms\Tables\ProductecomsTable;
use App\Models\Productecom;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductecomResource extends Resource
{
    protected static ?string $model = Productecom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

protected static ?string $navigationLabel = 'Products';
protected static ?string $modelLabel = 'Product';
protected static ?string $pluralModelLabel = 'Products';

protected static string | \UnitEnum | null $navigationGroup = 'Ecommerce Panel';
protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ProductecomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductecomsTable::configure($table);
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
        ->with('orderTypes');
}
    public static function getPages(): array
    {
        return [
            'index' => ListProductecoms::route('/'),
            'create' => CreateProductecom::route('/create'),
            'edit' => EditProductecom::route('/{record}/edit'),
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

    return $user->hasPermission('products.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}

}


