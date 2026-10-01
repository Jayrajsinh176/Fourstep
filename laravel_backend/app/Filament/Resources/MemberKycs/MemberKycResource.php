<?php

namespace App\Filament\Resources\MemberKycs;

use App\Models\MyKyc;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\MemberKycs\Pages\ListMemberKycs;
use App\Filament\Resources\MemberKycs\Tables\MemberKycsTable;

class MemberKycResource extends Resource
{
    protected static ?string $model = MyKyc::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'KYC Verification';

protected static ?string $pluralLabel = 'KYC Verification';
protected static ?string $modelLabel = 'KYC Verification';
    // ✅ FIXED TYPE HERE
    protected static string|\UnitEnum|null $navigationGroup = 'Member Panel';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'user_id';

    public static function table(Table $table): Table
    {
        return MemberKycsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberKycs::route('/'),
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

    return $user->hasPermission('member_kyc.view');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
    
}