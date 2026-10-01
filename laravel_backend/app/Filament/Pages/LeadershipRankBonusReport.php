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
use Illuminate\Support\Facades\Auth;

class LeadershipRankBonusReport extends Page implements HasTable
{
    use InteractsWithTable;

    public static function canAccess(): bool
    {
        return Auth::user()?->hasPermission('leadership_rank_report') ?? false;
    }

    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationLabel = 'Leadership Rank ';

    
    protected static ?string $title = 'Leadership Gift Bonus Report';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.leadership-rank-bonus-report';

    protected function getTableQuery(): Builder
    {
        return LeadershipGiftBonus::query()
            ->with('member')
            ->latest('qualified_date');
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

            Tables\Columns\TextColumn::make('rank_name')
                ->label('Rank')
                ->badge()
                ->color('success'),

            Tables\Columns\TextColumn::make('gift_name')
                ->label('Gift')
                ->badge()
                ->color('info')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'pending' => 'warning',
                    'approved' => 'info',
                    'delivered' => 'success',
                    default => 'gray',
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('qualified_date')
                ->label('Qualified Date')
                ->date('d M Y')
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
                    'delivered' => 'Delivered',
                ]),

            Tables\Filters\SelectFilter::make('rank_name')
                ->label('Rank')
                ->options(
                    LeadershipGiftBonus::query()
                        ->orderBy('rank_name')
                        ->pluck('rank_name', 'rank_name')
                        ->unique()
                        ->toArray()
                ),

            Tables\Filters\SelectFilter::make('gift_name')
                ->label('Gift')
                ->options(
                    LeadershipGiftBonus::query()
                        ->orderBy('gift_name')
                        ->pluck('gift_name', 'gift_name')
                        ->unique()
                        ->toArray()
                ),

            Tables\Filters\Filter::make('qualified_date')
                ->form([
                    DatePicker::make('from')
                        ->label('From Date'),

                    DatePicker::make('until')
                        ->label('To Date'),
                ])
                ->query(function (Builder $query, array $data) {
                    return $query
                        ->when(
                            $data['from'],
                            fn ($q) =>
                                $q->whereDate(
                                    'qualified_date',
                                    '>=',
                                    $data['from']
                                )
                        )
                        ->when(
                            $data['until'],
                            fn ($q) =>
                                $q->whereDate(
                                    'qualified_date',
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
                        'admin.leadership-rank-bonus-report.pdf',
                        [
                            'from_date' => $data['from_date'],
                            'to_date'   => $data['to_date'],
                        ]
                    );

                }),

        ];
    }
}