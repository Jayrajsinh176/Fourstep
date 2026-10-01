<?php

namespace App\Filament\Pages;

use App\Models\PurchaseBonusTransaction;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class GroupBuiltupBonusReport extends Page implements HasTable
{
    use InteractsWithTable;

public static function canAccess(): bool
{
    return Auth::user()?->hasPermission('group_builtup_bonus_report') ?? false;
}

    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Purchase Activation Bonus';

        protected static ?string $title = 'Purchase Activation Bonus Report';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.group-builtup-bonus-report';

    protected function getTableQuery(): Builder
    {
return PurchaseBonusTransaction::query()
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
    ->formatStateUsing(function ($state, $record) {

        return $state .
            '<br><small>(M: ' .
            ($record->member->mobile_no ?? '-') .
            ')</small>';

    })
    ->html()
    ->searchable()
    ->sortable(),

   Tables\Columns\TextColumn::make('week_start')
    ->label('Week Start')
    ->date('d-m-Y')
    ->sortable(),

Tables\Columns\TextColumn::make('week_end')
    ->label('Week End')
    ->date('d-m-Y')
    ->sortable(),

Tables\Columns\TextColumn::make('left_bv_before')
    ->label('Left BV')
    ->numeric(decimalPlaces: 2)
    ->sortable(),

Tables\Columns\TextColumn::make('right_bv_before')
    ->label('Right BV')
    ->numeric(decimalPlaces: 2)
    ->sortable(),

Tables\Columns\TextColumn::make('matching_bv')
    ->label('Matching BV')
    ->numeric(decimalPlaces: 2),

Tables\Columns\TextColumn::make('matched_pairs')
    ->label('Pairs'),

Tables\Columns\TextColumn::make('gross_income')
    ->label('Gross Income')
    ->money('INR'),

Tables\Columns\TextColumn::make('weekly_cap')
    ->label('Weekly Cap')
    ->money('INR'),

Tables\Columns\TextColumn::make('payable_income')
    ->label('Payable Bonus')
    ->money('INR')
    ->color('success')
    ->weight('bold'),

        Tables\Columns\TextColumn::make('status')
            ->badge()
            ->color(fn ($state) => match ($state) {
                'pending' => 'warning',
                'approved' => 'info',
                'paid' => 'success',
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
                'approved' => 'Approved',
                'paid' => 'Paid',
            ]),

        Tables\Filters\SelectFilter::make('step_level')
            ->label('Step')
            ->options(
              PurchaseBonusTransaction::query()
                    ->orderBy('step_level')
                    ->pluck('step_level', 'step_level')
                    ->unique()
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
                DatePicker::make('from_date')
                    ->label('From Date')
                    ->required(),

                DatePicker::make('to_date')
                    ->label('To Date')
                    ->required(),
            ])
            ->action(function (array $data) {

                return redirect()->route(
                    'admin.group-builtup-bonus-report.pdf',
                    [
                        'from_date' => $data['from_date'],
                        'to_date'   => $data['to_date'],
                    ]
                );

            }),

    ];
}

}