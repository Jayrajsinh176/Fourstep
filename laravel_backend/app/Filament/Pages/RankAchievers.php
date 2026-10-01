<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\LeadershipRankBonus;

use Filament\Forms\Components\DatePicker;

class RankAchievers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \UnitEnum | null $navigationGroup = 'Member Panel';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Rank Achievers';
    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.rank-achievers';

    // ✅ QUERY
    protected function getTableQuery(): Builder
    {
        return LeadershipRankBonus::query()
            ->with('upline') // IMPORTANT for user_id
            ->latest('rank_achieved_date');
    }

    // ✅ COLUMNS (CLEAN + ADMIN READY)
    protected function getTableColumns(): array
    {
        return [

        Tables\Columns\TextColumn::make('sr_no')
            ->label('Sr No.')
            ->rowIndex(),
            
            Tables\Columns\TextColumn::make('upline.user_id')
                ->label('Member ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('rank_name')
                ->label('Rank')
                ->badge()
                ->color('success')
                ->sortable(),

            Tables\Columns\TextColumn::make('bonus_amount')
                ->label('Bonus')
                ->money('INR')
                ->sortable(),

            Tables\Columns\TextColumn::make('generation_no')
                ->label('Gen')
                ->sortable(),

            Tables\Columns\TextColumn::make('matching_income')
                ->label('Matching Income')
                ->money('INR')
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('cycle_date')
                ->label('Cycle Date')
                ->date()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('rank_achieved_date')
                ->label('Achieved Date')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn ($state) => $state === 'paid' ? 'success' : 'warning'),
        ];
    }

    // ✅ FILTERS (VERY IMPORTANT FOR ADMIN)
    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\SelectFilter::make('rank_name')
                ->label('Rank')
                ->options([
                    'MANAGER' => 'Manager',
                    'AREA MANAGER' => 'Area Manager',
                    'ZONAL MANAGER' => 'Zonal Manager',
                    'REGIONAL MANAGER' => 'Regional Manager',
                ]),

            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                ]),

            Tables\Filters\SelectFilter::make('generation_no')
                ->label('Generation')
                ->options([
                    1 => '1st',
                    2 => '2nd',
                    3 => '3rd',
                    4 => '4th',
                    5 => '5th',
                    6 => '6th',
                ]),

            Tables\Filters\Filter::make('date_range')
                ->form([
                    DatePicker::make('from'),
DatePicker::make('until'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn ($q) => $q->whereDate('rank_achieved_date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('rank_achieved_date', '<=', $data['until']));
                }),
        ];
    }
public static function canAccess(): bool
{
    return false;
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
  
   
}