<?php

namespace App\Filament\Pages;

use App\Models\LoyaltyBonus;
use Filament\Pages\Page;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class RepurchaseBonusReport extends Page implements HasTable
{
    use InteractsWithTable;
    
public static function canAccess(): bool
{
    return Auth::user()?->hasPermission('repurchase_bonus_report') ?? false;
}

    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Group Purchase Bonus';

     protected static ?string $title = 'Group Purchase Bonus Report';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.repurchase-bonus-report';
    
protected function getTableQuery(): Builder
{
    return LoyaltyBonus::query()
        ->with('member')
        ->where('type', 'repurchase')
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
    ->label('Bonus Week')
    ->formatStateUsing(function ($state, $record) {
        return \Carbon\Carbon::parse($record->week_start)->format('d M Y')
            . ' - ' .
            \Carbon\Carbon::parse($record->week_end)->format('d M Y');
    })
    ->badge()
    ->color('primary'),

        Tables\Columns\TextColumn::make('purchase_amount')
            ->label('Purchase Amount')
            ->money('INR'),

        Tables\Columns\TextColumn::make('bonus_amount')
            ->label('Purchase Bonus')
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

        Tables\Filters\Filter::make('calculated_at')
            ->form([
                DatePicker::make('from')
                    ->label('From Date'),

                DatePicker::make('until')
                    ->label('To Date'),
            ])
            ->query(function (Builder $query, array $data) {
                return $query
                    ->when(
                        $data['from'] ?? null,
                        fn ($q) => $q->whereDate(
                            'calculated_at',
                            '>=',
                            $data['from']
                        )
                    )
                    ->when(
                        $data['until'] ?? null,
                        fn ($q) => $q->whereDate(
                            'calculated_at',
                            '<=',
                            $data['until']
                        )
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
                    'admin.repurchase-bonus-report.pdf',
                    [
                        'from_date' => $data['from_date'],
                        'to_date' => $data['to_date'],
                    ]
                );

            }),

    ];
}
}