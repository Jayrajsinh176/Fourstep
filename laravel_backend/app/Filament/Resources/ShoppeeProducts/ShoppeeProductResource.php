<?php

namespace App\Filament\Resources\ShoppeeProducts;

use App\Filament\Resources\ShoppeeProducts\Pages\CreateShoppeeProduct;
use App\Filament\Resources\ShoppeeProducts\Pages\EditShoppeeProduct;
use App\Filament\Resources\ShoppeeProducts\Pages\ListShoppeeProducts;
use App\Filament\Resources\ShoppeeProducts\Pages\ViewShoppeeProduct;
use App\Filament\Resources\ShoppeeProducts\Schemas\ShoppeeProductForm;
use App\Filament\Resources\ShoppeeProducts\Schemas\ShoppeeProductInfolist;
use App\Filament\Resources\ShoppeeProducts\Tables\ShoppeeProductsTable;
use App\Models\Shoppee_Product;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShoppeeProductResource extends Resource
{
    protected static ?string $model = Shoppee_Product::class;

    protected static bool $shouldRegisterNavigation = false;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

protected static string | \UnitEnum | null $navigationGroup = 'Shoppee Panel';

protected static ?string $navigationLabel = 'Products';
protected static ?string $pluralLabel = 'Products';
protected static ?string $modelLabel = 'Product';

protected static ?int $navigationSort = 3;
    public static function form(Schema $schema): Schema
    {
        return ShoppeeProductForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShoppeeProductInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoppeeProductsTable::configure($table);
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
            'index' => ListShoppeeProducts::route('/'),
            'create' => CreateShoppeeProduct::route('/create'),
            'view' => ViewShoppeeProduct::route('/{record}'),
            'edit' => EditShoppeeProduct::route('/{record}/edit'),
        ];
    }
}
