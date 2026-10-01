<?php

namespace App\Filament\Resources\ShoppeeProductRequests;

use App\Models\Shoppee_ProductRequest;
use App\Filament\Resources\ShoppeeProductRequests\Pages\ListShoppeeProductRequests;
use App\Filament\Resources\ShoppeeProductRequests\Pages\ViewShoppeeProductRequest;
use App\Filament\Resources\ShoppeeProductRequests\Pages\EditShoppeeProductRequest;
use App\Filament\Resources\ShoppeeProductRequests\Schemas\ShoppeeProductRequestForm;
use App\Filament\Resources\ShoppeeProductRequests\Schemas\ShoppeeProductRequestInfolist;
use App\Filament\Resources\ShoppeeProductRequests\Tables\ShoppeeProductRequestsTable;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
class ShoppeeProductRequestResource extends Resource
{
    protected static ?string $model = Shoppee_ProductRequest::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Shoppee Panel';

protected static ?string $navigationLabel = 'Product Requests';
protected static ?string $pluralLabel = 'Product Requests';
protected static ?string $modelLabel = 'Product Request';

protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ShoppeeProductRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ShoppeeProductRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoppeeProductRequestsTable::configure($table);
    }

    public static function canCreate(): bool
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
            'index' => ListShoppeeProductRequests::route('/'),
            'view' => ViewShoppeeProductRequest::route('/{record}'),
            'edit' => EditShoppeeProductRequest::route('/{record}/edit'),
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

    return $user->hasPermission('product_requests.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}

}