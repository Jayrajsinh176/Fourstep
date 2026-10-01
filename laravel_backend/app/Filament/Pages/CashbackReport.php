<?php

namespace App\Filament\Pages;

use App\Models\CashbackWallet;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class CashbackReport extends Page implements HasTable
{
    use InteractsWithTable;
    
public static function canAccess(): bool
{
    return Auth::user()?->hasPermission('cashback_report') ?? false;
}

    protected static string | \UnitEnum | null $navigationGroup = 'Income Reports';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'Cashback Report';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.cashback-report';

    protected function getTableQuery(): Builder
    {
        return CashbackWallet::query()
            ->with('member')
            ->latest();
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

        Tables\Columns\TextColumn::make('detail')
            ->label('Description')
            ->searchable()
            ->wrap(),

        Tables\Columns\TextColumn::make('credit')
            ->label('Credit')
            ->money('INR')
            ->color('success')
            ->weight('bold'),

        Tables\Columns\TextColumn::make('debit')
            ->label('Debit')
            ->money('INR')
            ->color('danger')
            ->weight('bold'),

        Tables\Columns\TextColumn::make('created_at')
            ->label('Transaction Date')
            ->dateTime('d M Y h:i A')
            ->sortable(),

    ];
}

protected function getTableFilters(): array
{
    return [

        Tables\Filters\Filter::make('credit')
            ->label('Credit Transactions')
            ->query(fn (Builder $query) => $query->where('credit', '>', 0)),

        Tables\Filters\Filter::make('debit')
            ->label('Debit Transactions')
            ->query(fn (Builder $query) => $query->where('debit', '>', 0)),

        Tables\Filters\Filter::make('created_at')
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
                        fn ($q) => $q->whereDate('created_at', '>=', $data['from'])
                    )
                    ->when(
                        $data['until'],
                        fn ($q) => $q->whereDate('created_at', '<=', $data['until'])
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
                    'admin.cashback-report.pdf',
                    [
                        'from_date' => $data['from_date'],
                        'to_date'   => $data['to_date'],
                    ]
                );

            }),

    ];
}

}