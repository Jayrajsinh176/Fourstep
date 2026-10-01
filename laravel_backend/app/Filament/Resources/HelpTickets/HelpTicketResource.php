<?php

namespace App\Filament\Resources\HelpTickets;

use App\Filament\Resources\HelpTickets\Pages\CreateHelpTicket;
use App\Filament\Resources\HelpTickets\Pages\EditHelpTicket;
use App\Filament\Resources\HelpTickets\Pages\ListHelpTickets;
use App\Filament\Resources\HelpTickets\Schemas\HelpTicketForm;
use App\Filament\Resources\HelpTickets\Tables\HelpTicketsTable;
use App\Models\HelpTicket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HelpTicketResource extends Resource
{
    protected static ?string $model = HelpTicket::class;
protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

protected static ?string $recordTitleAttribute = 'subject';

protected static ?string $navigationLabel = 'Help Tickets';
protected static string | \UnitEnum | null $navigationGroup = 'Ecommerce Panel';
protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return HelpTicketForm::form($schema);
    }

    public static function table(Table $table): Table
    {
        return HelpTicketsTable::table($table); 
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
            'index' => ListHelpTickets::route('/'),
            'create' => CreateHelpTicket::route('/create'),
            'edit' => EditHelpTicket::route('/{record}/edit'),
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

    return $user->hasPermission('help_tickets.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
}
