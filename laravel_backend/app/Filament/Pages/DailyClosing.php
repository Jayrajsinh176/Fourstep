<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Actions\Action as TableAction;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

use App\Services\ActivityLogService;
use App\Services\WeeklyClosingService;
use App\Models\WeeklyClosing as WeeklyClosingModel;

class DailyClosing extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \UnitEnum | null $navigationGroup = 'Member Panel';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Weekly Closing';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'Weekly Closing';
    
  protected string $view = 'filament.pages.daily-closing';

    protected function getHeaderActions(): array
    {
        return [

            Action::make('run_weekly_closing')
                ->label('Run Weekly Closing')
                ->color('success')
                ->icon('heroicon-o-play')
                ->requiresConfirmation()
                ->action(function () {

                    $result = app(WeeklyClosingService::class)->run();

                    if (!$result['status']) {
                        Notification::make()
                            ->title('Warning')
                            ->body($result['message'])
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Weekly Closing Completed')
                        ->body(
                            "Processed: {$result['processed']} | Total: ₹" .
                            number_format($result['total_income'], 2)
                        )
                        ->success()
                        ->send();

                    ActivityLogService::log(
                        'Member Panel',
                        'Weekly Closing',
                        'Executed Weekly Closing. Processed ' .
                        $result['processed'] .
                        ' members. Total Income ₹' .
                        number_format($result['total_income'], 2)
                    );

                    $this->resetTable();
                }),

        ];
    }

    protected function getTableQuery(): Builder
    {
        return WeeklyClosingModel::query()
            ->with('member')
            ->latest('id');
    }

    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('member.user_id')
                ->label('Member ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('left_bv')
                ->label('Left BV')
                ->numeric(decimalPlaces: 2)
                ->sortable(),

            Tables\Columns\TextColumn::make('right_bv')
                ->label('Right BV')
                ->numeric(decimalPlaces: 2)
                ->sortable(),

            Tables\Columns\TextColumn::make('matched_bv')
                ->label('Matching BV')
                ->numeric(decimalPlaces: 2)
                ->badge()
                ->color('success')
                ->sortable(),

            Tables\Columns\TextColumn::make('income')
                ->label('Income')
                ->money('INR')
                ->sortable(),

            Tables\Columns\TextColumn::make('carry_left')
                ->label('Carry Left')
                ->numeric(decimalPlaces: 2)
                ->sortable(),

            Tables\Columns\TextColumn::make('carry_right')
                ->label('Carry Right')
                ->numeric(decimalPlaces: 2)
                ->sortable(),

            Tables\Columns\TextColumn::make('week_start')
                ->label('Week Start')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('week_end')
                ->label('Week End')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->colors([
                    'warning' => 'pending',
                    'success' => 'paid',
                ])
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\Filter::make('week_range')
                ->form([

                    \Filament\Forms\Components\DatePicker::make('from')
                        ->label('Week Start'),

                    \Filament\Forms\Components\DatePicker::make('until')
                        ->label('Week End'),

                ])
                ->query(function ($query, array $data) {

                    return $query
                        ->when(
                            $data['from'],
                            fn ($q) => $q->whereDate(
                                'week_start',
                                '>=',
                                $data['from']
                            )
                        )
                        ->when(
                            $data['until'],
                            fn ($q) => $q->whereDate(
                                'week_end',
                                '<=',
                                $data['until']
                            )
                        );
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [

            TableAction::make('approve')
                ->label('Approve')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn ($record) => $record->status === 'pending')
                ->requiresConfirmation()

                ->action(function ($record) {

                    if ($record->status === 'paid') {

                        Notification::make()
                            ->title('Already Approved')
                            ->warning()
                            ->send();

                        return;
                    }

                    DB::beginTransaction();

                    try {

                        /*
                        |--------------------------------------------------------------------------
                        | Mark Purchase Bonus as Paid
                        |--------------------------------------------------------------------------
                        */

                        $updated = DB::table('purchase_bonus_transactions')
                            ->where('id', $record->purchase_bonus_id)
                            ->where('member_id', $record->member_id)
                            ->where('status', 'approved')
                            ->update([
                                'status' => 'paid',
                                'updated_at' => now(),
                            ]);

                        if ($updated === 0) {

                            DB::rollBack();

                            Notification::make()
                                ->title('No matching Purchase Bonus found')
                                ->body(
                                    'The related Purchase Bonus is not in approved status.'
                                )
                                ->danger()
                                ->send();

                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Credit Member Wallet
                        |--------------------------------------------------------------------------
                        */

                        DB::table('members')
                            ->where('id', $record->member_id)
                            ->increment(
                                'wallet_balance',
                                $record->income
                            );

                        /*
                        |--------------------------------------------------------------------------
                        | Mark E-Wallet Log as Paid
                        |--------------------------------------------------------------------------
                        */

                        DB::table('ewallet_logs')
                            ->where('member_id', $record->member_id)
                            ->where('type', 'binary_income')
                            ->where('remark', 'Purchase Bonus')
                            ->where('amount', $record->income)
                            ->where('status', 'pending')
                            ->whereDate(
                                'created_at',
                                $record->week_end
                            )
                            ->update([
                                'status' => 'paid',
                            ]);

                        /*
                        |--------------------------------------------------------------------------
                        | Mark Weekly Closing as Paid
                        |--------------------------------------------------------------------------
                        */

                        DB::table('weekly_closings')
                            ->where('id', $record->id)
                            ->where('status', 'pending')
                            ->update([
                                'status' => 'paid',
                                'updated_at' => now(),
                            ]);

                        DB::commit();

                        ActivityLogService::log(
                            'Member Panel',
                            'Weekly Closing Approved',
                            'Approved Weekly Closing for member ' .
                            $record->member->user_id .
                            ' (' .
                            $record->member->fullname .
                            ')' .
                            ' | Amount ₹' .
                            number_format(
                                $record->income,
                                2
                            )
                        );

                        Notification::make()
                            ->title('Income Approved')
                            ->body(
                                '₹' .
                                number_format(
                                    $record->income,
                                    2
                                ) .
                                ' credited successfully.'
                            )
                            ->success()
                            ->send();

                        $this->resetTable();

                    } catch (\Exception $e) {

                        DB::rollBack();

                        Notification::make()
                            ->title('Approval Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        return $user->hasPermission('daily_closing');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}