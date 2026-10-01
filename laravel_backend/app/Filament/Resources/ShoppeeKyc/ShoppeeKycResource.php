<?php

namespace App\Filament\Resources\ShoppeeKyc;

use App\Models\Shoppee_Kyc;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Resources\ShoppeeKyc\Pages\ListShoppeeKyc;

class ShoppeeKycResource extends Resource
{
    protected static ?string $model = Shoppee_Kyc::class;

    protected static ?string $slug = 'shoppee-kyc';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup =
        'Shoppee Panel';
        
       
protected static ?string $pluralLabel = 'KYC Verification';
protected static ?string $modelLabel = 'KYC Verification';

    protected static ?string $navigationLabel = 'KYC Verification';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'member_name';

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\ShoppeeKyc\Tables\ShoppeeKycTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShoppeeKyc::route('/'),
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

    return $user->hasPermission('shoppee_kyc.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
}