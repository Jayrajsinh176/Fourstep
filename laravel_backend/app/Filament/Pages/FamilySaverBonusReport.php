<?php

namespace App\Filament\Pages;

use App\Models\FamilySaverBonus;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class FamilySaverBonusReport extends Page implements HasTable
{
    use InteractsWithTable;
    
public static function canAccess(): bool
{
    return Auth::user()?->hasPermission('family_saver_bonus_report') ?? false;
}


    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Family Saver Bonus';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.family-saver-bonus-report';


    protected function getTableQuery(): Builder
    {
        return FamilySaverBonus::query()
            ->with(['nominee', 'deceased'])
            ->latest('calculated_at');
    }


    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('id')
                ->label('Sr No.')
                ->rowIndex(),

            Tables\Columns\TextColumn::make('nominee.user_id')
                ->label('Nominee ID')
                ->searchable()
                ->sortable(),

         Tables\Columns\TextColumn::make('nominee.fullname')
    ->label('Nominee Name')
    ->formatStateUsing(function ($state, $record) {

        return $state .
            '<br><small>(M: ' .
            ($record->nominee->mobile_no ?? '-') .
            ')</small>';

    })
    ->html()
    ->searchable()
    ->sortable(),

            Tables\Columns\TextColumn::make('deceased.user_id')
                ->label('Deceased ID')
                ->searchable()
                ->sortable(),

          Tables\Columns\TextColumn::make('deceased.fullname')
    ->label('Deceased Name')
    ->formatStateUsing(function ($state, $record) {

        return $state .
            '<br><small>(M: ' .
            ($record->deceased->mobile_no ?? '-') .
            ')</small>';

    })
    ->html()
    ->searchable()
    ->sortable(),

            Tables\Columns\TextColumn::make('month_key')
                ->label('Bonus Month')
                ->badge()
                ->color('primary'),


            Tables\Columns\TextColumn::make('monthly_company_bv')
                ->label('Company BV')
                ->numeric(2),


            Tables\Columns\TextColumn::make('bonus_percentage')
                ->label('Bonus %')
                ->suffix('%'),


            Tables\Columns\TextColumn::make('bonus_amount')
                ->label('Bonus Amount')
                ->money('INR')
                ->color('success')
                ->weight('bold'),


            Tables\Columns\TextColumn::make('qualification_status')
                ->label('Qualification')
                ->badge()
                ->color('success'),


            Tables\Columns\TextColumn::make('status')
                ->badge(),


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


            Tables\Filters\SelectFilter::make('qualification_status')
                ->label('Qualification')
                ->options([
                    'Qualified' => 'Qualified',
                    'Not Qualified' => 'Not Qualified',
                ]),


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
                        'admin.family-saver-bonus-report.pdf',
                        [
                            'from_date' => $data['from_date'],
                            'to_date'   => $data['to_date'],
                        ]
                    );

                }),

        ];
    }
}