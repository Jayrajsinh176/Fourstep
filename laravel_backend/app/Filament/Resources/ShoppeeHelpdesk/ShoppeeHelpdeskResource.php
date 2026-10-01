<?php

namespace App\Filament\Resources\ShoppeeHelpdesk;

use App\Models\Shoppee_Helpdesk;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

use App\Filament\Resources\ShoppeeHelpdesk\Pages\ManageShoppeeHelpdesks;
use App\Filament\Resources\ShoppeeHelpdesk\Schemas\ShoppeeHelpdeskForm;
use App\Filament\Resources\ShoppeeHelpdesk\Tables\ShoppeeHelpdeskTable;

class ShoppeeHelpdeskResource extends Resource
{
    protected static ?string $model = Shoppee_Helpdesk::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Shoppee Panel';

    protected static ?string $navigationLabel = 'Helpdesk';

    protected static ?string $pluralLabel = 'HelpDesk';

    protected static ?string $modelLabel = 'HelpDesk';

    protected static ?int $navigationSort = 6;
protected static ?string $slug = 'shoppee-helpdesk';


    public static function form(Schema $schema): Schema
    {
        return ShoppeeHelpdeskForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShoppeeHelpdeskTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageShoppeeHelpdesks::route('/'),
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

    return $user->hasPermission('shoppee_helpdesk.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
}