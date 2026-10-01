<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\RewardAchiever;
use Filament\Actions\Action;

class RewardAchievers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \UnitEnum | null $navigationGroup = 'Member Panel';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Reward Achievers';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.reward-achievers';

    protected function getTableQuery(): Builder
    {
        return RewardAchiever::query()
            ->with(['member', 'reward'])
            ->latest('achieved_at');
    }

    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('sr_no')
                ->label('Sr No.')
                ->rowIndex(),

            Tables\Columns\TextColumn::make('member.user_id')
                ->label('Member ID')
                ->searchable(),

            Tables\Columns\TextColumn::make('reward.rank_name')
                ->label('Reward')
                ->badge()
                ->color('success'),

            Tables\Columns\TextColumn::make('reward.target_amount')
                ->label('Target')
                ->money('INR'),

            Tables\Columns\TextColumn::make('achieved_at')
                ->date(),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn ($state) =>
                    match ($state) {
                        'approved' => 'success',
                        'dispatched' => 'info',
                        'delivered' => 'primary',
                        'rejected' => 'danger',
                        default => 'warning',
                    }
                ),
        ];
    }

    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'dispatched' => 'Dispatched',
                    'delivered' => 'Delivered',
                    'rejected' => 'Rejected',
                ]),

            Tables\Filters\SelectFilter::make('reward_tier_id')
                ->label('Reward')
                ->relationship('reward', 'rank_name'),
        ];
    }

    protected function getTableActions(): array
    {
        return [

            Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'approved',
                    ]);
                })
                ->successNotificationTitle('Reward approved successfully'),

            Action::make('dispatch')
                ->label('Dispatch')
                ->icon('heroicon-o-truck')
                ->color('info')
                ->visible(fn ($record) => $record->status === 'approved')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'dispatched',
                    ]);
                })
                ->successNotificationTitle('Reward dispatched successfully'),

            Action::make('deliver')
                ->label('Delivered')
                ->icon('heroicon-o-gift')
                ->color('primary')
                ->visible(fn ($record) => $record->status === 'dispatched')
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'delivered',
                    ]);
                })
                ->successNotificationTitle('Reward marked as delivered'),

            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) =>
                    ! in_array($record->status, ['rejected', 'delivered'])
                )
                ->requiresConfirmation()
                ->action(function ($record) {
                    $record->update([
                        'status' => 'rejected',
                    ]);
                })
                ->successNotificationTitle('Reward rejected'),
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

        return $user->hasPermission('reward_achievers');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}