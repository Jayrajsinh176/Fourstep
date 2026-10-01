<?php

namespace App\Filament\Pages;

use App\Models\RewardAchiever;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class RankRewardReport extends Page implements HasTable
{
    use InteractsWithTable;
    
public static function canAccess(): bool
{
    return Auth::user()?->hasPermission('rank_reward_report') ?? false;
}

    protected static string|\UnitEnum|null $navigationGroup = 'Income Reports';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Rank Reward';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.rank-reward-report';

    protected function getTableQuery(): Builder
    {
        return RewardAchiever::query()
            ->with(['member', 'reward'])
            ->latest('achieved_at');
    }

    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('id')
                ->label('Sr No.')
                ->rowIndex(),

            Tables\Columns\TextColumn::make('member.user_id')
                ->label('Member ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('member.fullname')
    ->label('Member Name')
    ->formatStateUsing(function ($state, $record) {

        return $state .
            '<br><small>(M: ' .
            ($record->member->mobile_no ?? '-') .
            ')</small>';

    })
    ->html()
    ->searchable()
    ->sortable(),

            Tables\Columns\TextColumn::make('reward.rank_name')
                ->label('Rank Reward')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('reward.target_amount')
                ->label('Target Amount')
                ->money('INR')
                ->sortable(),

            Tables\Columns\TextColumn::make('achieved_at')
                ->label('Achievement Date')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn(string $state): string => match ($state) {
                    'approved' => 'success',
                    'pending' => 'warning',
                    'dispatched' => 'info',
                    'delivered' => 'primary',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('admin_note')
                ->label('Admin Note')
                ->limit(40)
                ->toggleable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\SelectFilter::make('status')
                ->label('Status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'dispatched' => 'Dispatched',
                    'delivered' => 'Delivered',
                ]),

            Tables\Filters\Filter::make('achieved_at')
                ->form([
                    \Filament\Forms\Components\DatePicker::make('from')
                        ->label('From Date'),

                    \Filament\Forms\Components\DatePicker::make('until')
                        ->label('To Date'),
                ])
                ->query(function (Builder $query, array $data) {

                    return $query
                        ->when(
                            $data['from'],
                            fn($q) => $q->whereDate('achieved_at', '>=', $data['from'])
                        )
                        ->when(
                            $data['until'],
                            fn($q) => $q->whereDate('achieved_at', '<=', $data['until'])
                        );

                }),

        ];
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->form([

                    \Filament\Forms\Components\DatePicker::make('from_date')
                        ->label('From Date')
                        ->required(),

                    \Filament\Forms\Components\DatePicker::make('to_date')
                        ->label('To Date')
                        ->required(),

                ])
                ->action(function (array $data) {

                    return redirect()->route(
                        'admin.rank-reward-report.pdf',
                        [
                            'from_date' => $data['from_date'],
                            'to_date'   => $data['to_date'],
                        ]
                    );

                }),

        ];
    }
}