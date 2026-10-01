<?php

namespace App\Filament\Pages;

use App\Models\BusinessMonitoringBonus;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class BusinessMonitoringBonusReport extends Page implements HasTable
{
    use InteractsWithTable;
    
public static function canAccess(): bool
{
    return Auth::user()?->hasPermission('business_monitoring_report') ?? false;
}

    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Business Monitoring Bonus';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.business-monitoring-bonus-report';

    protected function getTableQuery(): Builder
    {
        return BusinessMonitoringBonus::query()
            ->with(['sponsor', 'downline'])
            ->latest('cycle_date');
    }

    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('id')
                ->label('Sr No.')
                ->rowIndex(),

            Tables\Columns\TextColumn::make('sponsor.user_id')
                ->label('Sponsor ID')
                ->searchable()
                ->sortable(),

         Tables\Columns\TextColumn::make('sponsor.fullname')
    ->label('Sponsor Name')
    ->formatStateUsing(function ($state, $record) {

        return $state .
            '<br><small>(M: ' .
            ($record->sponsor->mobile_no ?? '-') .
            ')</small>';

    })
    ->html()
    ->searchable()
    ->sortable(),

            Tables\Columns\TextColumn::make('downline.user_id')
                ->label('Downline ID')
                ->searchable()
                ->sortable(),

          Tables\Columns\TextColumn::make('downline.fullname')
    ->label('Downline Name')
    ->formatStateUsing(function ($state, $record) {

        return $state .
            '<br><small>(M: ' .
            ($record->downline->mobile_no ?? '-') .
            ')</small>';

    })
    ->html()
    ->searchable()
    ->sortable(),

            Tables\Columns\TextColumn::make('cycle_date')
                ->label('Cycle Date')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('matching_income')
                ->label('Matching Income')
                ->money('INR'),

            Tables\Columns\TextColumn::make('bonus_percentage')
                ->label('Bonus %')
                ->suffix('%'),

            Tables\Columns\TextColumn::make('bonus_amount')
                ->label('Bonus Amount')
                ->money('INR')
                ->color('success')
                ->weight('bold'),

            Tables\Columns\TextColumn::make('status')
                ->badge(),

        ];
    }

    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),

            Tables\Filters\Filter::make('cycle_date')
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
                            fn ($q) => $q->whereDate('cycle_date', '>=', $data['from'])
                        )
                        ->when(
                            $data['until'],
                            fn ($q) => $q->whereDate('cycle_date', '<=', $data['until'])
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
                        'admin.business-monitoring-bonus-report.pdf',
                        [
                            'from_date' => $data['from_date'],
                            'to_date'   => $data['to_date'],
                        ]
                    );

                }),

        ];
    }
}