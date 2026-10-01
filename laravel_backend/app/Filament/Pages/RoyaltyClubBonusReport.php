<?php

namespace App\Filament\Pages;

use App\Models\RoyaltyClubBonus;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class RoyaltyClubBonusReport extends Page implements HasTable
{
    use InteractsWithTable;
    
public static function canAccess(): bool
{
    return Auth::user()?->hasPermission('royalty_club_bonus_report') ?? false;
}

    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationLabel = 'Royalty Club Bonus';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.royalty-club-bonus-report';

    protected function getTableQuery(): Builder
    {
        return RoyaltyClubBonus::query()
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

        Tables\Columns\TextColumn::make('month_key')
            ->label('Bonus Month')
            ->badge()
            ->color('primary'),

        Tables\Columns\TextColumn::make('monthly_turnover')
            ->label('Monthly Turnover')
            ->money('INR'),

        Tables\Columns\TextColumn::make('pool_percentage')
            ->label('Pool %')
            ->suffix('%'),

        Tables\Columns\TextColumn::make('royalty_pool_amount')
            ->label('Royalty Pool')
            ->money('INR'),

        Tables\Columns\TextColumn::make('eligible_users_count')
            ->label('Eligible Users'),

        Tables\Columns\TextColumn::make('bonus_amount')
            ->label('Royalty Bonus')
            ->money('INR')
            ->color('success')
            ->weight('bold'),

        Tables\Columns\TextColumn::make('status')
            ->badge()
            ->color(fn ($state) => match ($state) {
                'pending' => 'warning',
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
                'paid' => 'Paid',
            ]),

        Tables\Filters\SelectFilter::make('month_key')
            ->label('Bonus Month')
            ->options(
                RoyaltyClubBonus::query()
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
                    'admin.royalty-club-bonus-report.pdf',
                    [
                        'from_date' => $data['from_date'],
                        'to_date'   => $data['to_date'],
                    ]
                );

            }),

    ];
}
}