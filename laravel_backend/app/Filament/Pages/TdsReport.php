<?php

namespace App\Filament\Pages;

use App\Models\PayoutDetail;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

class TdsReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';

    protected static string|\BackedEnum|null $navigationIcon =
        'heroicon-o-document-currency-rupee';

    protected static ?string $navigationLabel = 'TDS Report';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.tds-report';


    /*
    |--------------------------------------------------------------------------
    | ACCESS
    |--------------------------------------------------------------------------
    */

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        return $user->hasPermission('tds_report');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }


    /*
    |--------------------------------------------------------------------------
    | TABLE QUERY
    |--------------------------------------------------------------------------
    */

    protected function getTableQuery(): Builder
    {
        return PayoutDetail::query()
            ->with(['member', 'kyc'])
            ->where('payment_status', 'paid')
            ->where('tds', '>', 0)
            ->latest('paid_at');
    }


    /*
    |--------------------------------------------------------------------------
    | TABLE COLUMNS
    |--------------------------------------------------------------------------
    */

    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('paid_at')
                ->label('Payment Date')
                ->date('d-m-Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('member.user_id')
                ->label('Member ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('member.fullname')
                ->label('Name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('member.mobile_no')
                ->label('Mobile No.')
                ->searchable(),

            Tables\Columns\TextColumn::make('kyc.pan_number')
                ->label('PAN No.')
                ->searchable(),

            Tables\Columns\TextColumn::make('gross_amount')
                ->label('Gross Amount')
                ->money('INR')
                ->sortable(),

            Tables\Columns\TextColumn::make('tds')
                ->label('TDS')
                ->money('INR')
                ->sortable()
                ->weight('bold')
                ->color('danger'),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\Filter::make('paid_at')
                ->label('Payment Date')
                ->form([

                    Forms\Components\DatePicker::make('from')
                        ->label('From Date'),

                    Forms\Components\DatePicker::make('to')
                        ->label('To Date'),

                ])
                ->query(function (Builder $query, array $data) {

                    return $query
                        ->when(
                            $data['from'],
                            fn ($q) =>
                                $q->whereDate(
                                    'paid_at',
                                    '>=',
                                    $data['from']
                                )
                        )
                        ->when(
                            $data['to'],
                            fn ($q) =>
                                $q->whereDate(
                                    'paid_at',
                                    '<=',
                                    $data['to']
                                )
                        );

                }),

            Tables\Filters\Filter::make('minimum_tds')
                ->label('Minimum TDS')
                ->form([

                    Forms\Components\TextInput::make('amount')
                        ->label('Minimum TDS')
                        ->numeric(),

                ])
                ->query(function (Builder $query, array $data) {

                    return $query->when(
                        $data['amount'],
                        fn ($q) =>
                            $q->where(
                                'tds',
                                '>=',
                                $data['amount']
                            )
                    );

                }),

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT ACTIONS
    |--------------------------------------------------------------------------
    */

    protected function getHeaderActions(): array
    {
        return [

            Action::make('pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->form([

                    Forms\Components\DatePicker::make('from_date')
                        ->label('From Date')
                        ->required(),

                    Forms\Components\DatePicker::make('to_date')
                        ->label('To Date')
                        ->required(),

                ])
                ->action(function (array $data) {

                    return redirect()->route(
                        'admin.tds-report.pdf',
                        [
                            'from_date' => $data['from_date'],
                            'to_date'   => $data['to_date'],
                        ]
                    );

                }),

            Action::make('excel')
                ->label('Export Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->form([

                    Forms\Components\DatePicker::make('from_date')
                        ->label('From Date')
                        ->required(),

                    Forms\Components\DatePicker::make('to_date')
                        ->label('To Date')
                        ->required(),

                ])
                ->action(function (array $data) {

                    return redirect()->route(
                        'admin.tds-report.excel',
                        [
                            'from_date' => $data['from_date'],
                            'to_date'   => $data['to_date'],
                        ]
                    );

                }),

        ];
    }
}