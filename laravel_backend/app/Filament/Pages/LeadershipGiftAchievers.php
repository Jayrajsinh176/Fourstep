<?php

namespace App\Filament\Pages;

use App\Models\LeadershipGiftBonus;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;

class LeadershipGiftAchievers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \UnitEnum | null $navigationGroup = 'Member Panel';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Leadership Gift Achievers';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.leadership-gift-achievers';

    protected function getTableQuery(): Builder
    {
        return LeadershipGiftBonus::query()
            ->with('member')
            ->latest('qualified_date');
    }

    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('sr_no')
                ->label('Sr No.')
                ->rowIndex(),

            Tables\Columns\TextColumn::make('member.user_id')
                ->label('Member ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('member.fullname')
                ->label('Member Name')
                ->searchable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('rank_name')
                ->label('Rank')
                ->badge()
                ->color('success')
                ->sortable(),

            Tables\Columns\TextColumn::make('gift_name')
                ->label('Gift')
                ->badge()
                ->color('info')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('qualified_date')
                ->label('Qualified Date')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'delivered' => 'success',
                    'approved' => 'info',
                    'pending' => 'warning',
                    default => 'gray',
                })
                ->sortable(),
        ];
    }

     protected function getTableFilters(): array
    {
        return [

            Tables\Filters\SelectFilter::make('rank_name')
                ->label('Rank')
                ->options([
                    'Manager' => 'Manager',
                    'Area Manager' => 'Area Manager',
                    'Zonal Manager' => 'Zonal Manager',
                    'Regional Manager' => 'Regional Manager',
                ]),

            Tables\Filters\SelectFilter::make('gift_name')
                ->label('Gift')
                ->options([
                    '4Step Diary' => '4Step Diary',
                    '4Step Tie' => '4Step Tie',
                    '4Step Backpack Bag' => '4Step Backpack Bag',
                    '4Step LDP + Domestic Tour' => '4Step LDP + Domestic Tour',
                ]),

            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'delivered' => 'Delivered',
                ]),

            Tables\Filters\Filter::make('date_range')
                ->form([
                    DatePicker::make('from'),
                    DatePicker::make('until'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when(
                            $data['from'],
                            fn ($q) => $q->whereDate(
                                'qualified_date',
                                '>=',
                                $data['from']
                            )
                        )
                        ->when(
                            $data['until'],
                            fn ($q) => $q->whereDate(
                                'qualified_date',
                                '<=',
                                $data['until']
                            )
                        );
                }),
        ];
    }

    protected function getTableActions(): array
{
    return [
        Action::make('changeStatus')
            ->label('Change Status')
            ->icon('heroicon-o-arrow-path')
            ->form([
                \Filament\Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'delivered' => 'Delivered',
                    ])
                    ->required(),
            ])
            ->fillForm(fn (LeadershipGiftBonus $record) => [
                'status' => $record->status,
            ])
            ->action(function (
                LeadershipGiftBonus $record,
                array $data
            ) {
                $record->update([
                    'status' => $data['status'],
                ]);
            })
            ->successNotificationTitle('Gift status updated successfully'),
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

        return $user->hasPermission('rank_achievers');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}