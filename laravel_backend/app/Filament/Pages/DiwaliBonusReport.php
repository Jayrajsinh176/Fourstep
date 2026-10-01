<?php

namespace App\Filament\Pages;

use App\Models\DiwaliBonus;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;

class DiwaliBonusReport extends Page implements HasTable
{
    use InteractsWithTable;
    public static function shouldRegisterNavigation(): bool
{
    return false;
}

    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Diwali Bonus';

    protected static ?int $navigationSort = 7;

    protected string $view = 'filament.pages.diwali-bonus-report';

    protected function getTableQuery(): Builder
    {
        return DiwaliBonus::query()
            ->with('member')
            ->latest('calculated_at');
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
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('bonus_year')
                ->label('Bonus Year')
                ->badge()
                ->color('primary'),

            Tables\Columns\TextColumn::make('period_start')
                ->label('Period Start')
                ->date('d M Y'),

            Tables\Columns\TextColumn::make('period_end')
                ->label('Period End')
                ->date('d M Y'),

            Tables\Columns\TextColumn::make('total_lapsed_pv')
                ->label('Lapsed PV')
                ->numeric(2),

            Tables\Columns\TextColumn::make('bonus_percentage')
                ->label('Bonus %')
                ->suffix('%'),

            Tables\Columns\TextColumn::make('bonus_amount')
                ->label('Bonus Amount')
                ->money('INR')
                ->color('success')
                ->weight('bold'),

            Tables\Columns\TextColumn::make('calculated_at')
                ->label('Calculated Date')
                ->dateTime('d M Y h:i A')
                ->sortable(),

        ];
    }

    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\SelectFilter::make('bonus_year')
                ->label('Bonus Year')
                ->options(
                    DiwaliBonus::query()
                        ->orderByDesc('bonus_year')
                        ->pluck('bonus_year', 'bonus_year')
                        ->toArray()
                ),

            Tables\Filters\Filter::make('calculated_at')
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
                            fn ($q) => $q->whereDate('calculated_at', '>=', $data['from'])
                        )
                        ->when(
                            $data['until'],
                            fn ($q) => $q->whereDate('calculated_at', '<=', $data['until'])
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
                        'admin.diwali-bonus-report.pdf',
                        [
                            'from_date' => $data['from_date'],
                            'to_date'   => $data['to_date'],
                        ]
                    );

                }),

        ];
    }
}