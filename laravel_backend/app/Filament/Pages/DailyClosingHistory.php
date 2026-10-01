<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Facades\DB;

class DailyClosingHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \UnitEnum | null $navigationGroup = 'Member Panel';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Weekly Closing History';

    protected static ?string $title = 'Weekly Closing History';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.daily-closing-history';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\WeeklyClosing::query()
                    ->select(
                        DB::raw('MIN(id) as id'),
                        'week_start',
                        'week_end',
                        DB::raw('COUNT(*) as total_members'),
                        DB::raw('SUM(matched_bv) as total_business_bv'),
                        DB::raw('SUM(income) as total_income')
                    )
                    ->groupBy('week_start', 'week_end')
                    ->orderByDesc('week_start')
            )
            ->columns([

                Tables\Columns\TextColumn::make('week_start')
                    ->label('Week Start')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('week_end')
                    ->label('Week End')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_members')
                    ->label('Total Members')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_business_bv')
                    ->label('Total Matching BV')
                    ->numeric(decimalPlaces: 2)
                    ->suffix(' BV')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_income')
                    ->label('Purchase Bonus')
                    ->money('INR')
                    ->alignEnd(),

            ])
            ->recordActions([

                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn ($record) => url(
                        '/admin/weekly-closing-details?week_start=' .
                        $record->week_start .
                        '&week_end=' .
                        $record->week_end
                    )),

            ]);
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

        return $user->hasPermission('daily_closing');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}