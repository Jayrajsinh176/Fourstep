<?php

namespace App\Filament\Resources\Categoryecoms;

use App\Filament\Resources\Categoryecoms\Pages\CreateCategoryecom;
use App\Filament\Resources\Categoryecoms\Pages\EditCategoryecom;
use App\Filament\Resources\Categoryecoms\Pages\ListCategoryecoms;
use App\Filament\Resources\Categoryecoms\Schemas\CategoryecomForm;
use App\Filament\Resources\Categoryecoms\Tables\CategoryecomsTable;
use App\Models\Categoryecom;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CategoryecomResource extends Resource
{
    protected static ?string $model = Categoryecom::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Categories';
    protected static ?string $modelLabel = 'Category';
    protected static ?string $pluralModelLabel = 'Categories';

    // ✅ ADD THIS
    protected static string | \UnitEnum | null $navigationGroup = 'Ecommerce Panel';
protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CategoryecomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryecomsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategoryecoms::route('/'),
            'create' => CreateCategoryecom::route('/create'),
            'edit' => EditCategoryecom::route('/{record}/edit'),
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

    return $user->hasPermission('categories');
}

public static function shouldRegisterNavigation(): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    if ($user->isOwner()) {
        return true;
    }

    return $user->hasPermission('categories');
}
}