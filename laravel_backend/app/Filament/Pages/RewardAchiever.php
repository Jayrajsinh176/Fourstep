<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\RewardAchiever;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use App\Services\ActivityLogService;

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

Tables\Columns\TextColumn::make('member.fullname')
    ->label('Member Name')
    ->searchable()
    ->sortable(),

   Tables\Columns\TextColumn::make('reward.rank_name')
    ->label('Reward')
    ->html()
    ->formatStateUsing(function ($state, $record) {
        return "<strong>{$state}</strong><br>{$record->reward->reward_description}";
    }),

           Tables\Columns\TextColumn::make('reward.target_amount')
    ->label('Target')
    ->money('INR', locale: 'en_IN'),

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

            // ✅ DATE FILTER
            Tables\Filters\Filter::make('achieved_at')
                ->form([
                    DatePicker::make('from')->label('From Date'),
                    DatePicker::make('until')->label('To Date'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q) =>
                            $q->whereDate('achieved_at', '>=', $data['from'])
                        )
                        ->when($data['until'], fn ($q) =>
                            $q->whereDate('achieved_at', '<=', $data['until'])
                        );
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [

            // ✅ APPROVE
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

    ActivityLogService::log(
        'Member Panel',
        'Reward Approved',
  'Approved "' . $record->reward->rank_name .
'" for member ' . $record->member->user_id .
' (' . $record->member->fullname . ')'
    );
}),

            // ✅ DISPATCH
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

    ActivityLogService::log(
        'Member Panel',
        'Reward Dispatched',
        'Dispatched "' . $record->reward->rank_name .
        '" for member ' . $record->member->user_id
    );
}),

            // ✅ DELIVER
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

    ActivityLogService::log(
        'Member Panel',
        'Reward Delivered',
        'Delivered "' . $record->reward->rank_name .
        '" to member ' . $record->member->user_id
    );
}),

            // ✅ REJECT (NEW)
            Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn ($record) => !in_array($record->status, ['rejected', 'delivered']))
                ->requiresConfirmation()
           ->action(function ($record) {

    $record->update([
        'status' => 'rejected',
    ]);

    ActivityLogService::log(
        'Member Panel',
        'Reward Rejected',
        'Rejected "' . $record->reward->rank_name .
        '" for member ' . $record->member->user_id
    );
}),
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