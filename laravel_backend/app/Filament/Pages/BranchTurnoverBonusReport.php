<?php

/*
 * Branch Turnover Bonus report is disabled — all Branch Turnover Bonus
 * pages/routes are commented out across the project. Kept for reference.
 *
namespace App\Filament\Pages;

use App\Models\BranchTurnoverBonus;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class BranchTurnoverBonusReport extends Page implements HasTable
{
    use InteractsWithTable;

public static function canAccess(): bool
{
    return Auth::user()?->hasPermission('branch_turnover_bonus_report') ?? false;
}

    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationLabel = 'Branch Turnover Bonus';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.branch-turnover-bonus-report';


    protected function getTableQuery(): Builder
    {
        return BranchTurnoverBonus::query()
            ->with('branch')
            ->latest('calculated_at');
    }


    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('id')
                ->label('Sr No.')
                ->rowIndex(),

            Tables\Columns\TextColumn::make('branch.name')
                ->label('Branch Name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('branch.code')
                ->label('Branch Code')
                ->searchable(),

            Tables\Columns\TextColumn::make('month_key')
                ->label('Bonus Month')
                ->badge()
                ->color('primary'),

            Tables\Columns\TextColumn::make('total_turnover')
                ->label('Total Turnover')
                ->money('INR'),

            Tables\Columns\TextColumn::make('commission_percentage')
                ->label('Commission %')
                ->suffix('%'),

            Tables\Columns\TextColumn::make('bonus_amount')
                ->label('Bonus Amount')
                ->money('INR')
                ->color('success')
                ->weight('bold'),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn ($state) => match (strtolower($state)) {
                    'approved', 'paid' => 'success',
                    'pending' => 'warning',
                    default => 'gray',
                }),

            Tables\Columns\TextColumn::make('calculated_at')
                ->label('Calculated Date')
                ->dateTime('d M Y h:i A')
                ->sortable(),

        ];
    }


    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'pending' => 'Pending',
                    'Approved' => 'Approved',
                    'paid' => 'Paid',
                ]),


            Tables\Filters\SelectFilter::make('month_key')
                ->label('Bonus Month')
                ->options(
                    BranchTurnoverBonus::query()
                        ->orderByDesc('month_key')
                        ->pluck('month_key', 'month_key')
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
                            fn ($q) =>
                            $q->whereDate('calculated_at','>=',$data['from'])
                        )
                        ->when(
                            $data['until'],
                            fn ($q) =>
                            $q->whereDate('calculated_at','<=',$data['until'])
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
                ->action(function(array $data){

                    return redirect()->route(
                        'admin.branch-turnover-bonus-report.pdf',
                        [
                            'from_date'=>$data['from_date'],
                            'to_date'=>$data['to_date'],
                        ]
                    );

                }),

        ];
    }
}
*/