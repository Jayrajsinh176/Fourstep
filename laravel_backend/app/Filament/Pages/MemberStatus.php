<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Member;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Services\ActivityLogService;

class MemberStatus extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string | \UnitEnum | null $navigationGroup = 'Member Panel';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user';
    protected static ?string $title = 'Active / Block Member';
    protected static ?string $navigationLabel = 'Active / Block';
    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.member-status';

    // =========================
    // TABLE QUERY
    // =========================
    protected function getTableQuery(): Builder
    {
        return Member::query()->latest();
    }

    // =========================
    // TABLE COLUMNS
    // =========================
    protected function getTableColumns(): array
    {
        return [

            Tables\Columns\TextColumn::make('user_id')
                ->label('Member ID')
                ->searchable(),

            Tables\Columns\TextColumn::make('fullname')
                ->label('Name')
                ->searchable(),

            Tables\Columns\TextColumn::make('account_status')
                ->label('Account')
                ->badge()
                ->color(fn ($state) => $state === 'active' ? 'success' : 'danger'),

            Tables\Columns\IconColumn::make('is_active')
                ->label('Activated')
                ->boolean(),

            Tables\Columns\TextColumn::make('package_step')
                ->label('Package')
                ->formatStateUsing(fn ($state) => $state ? 'P'.$state : '-'),
        ];
    }

    // =========================
    // FILTERS
    // =========================
    protected function getTableFilters(): array
    {
        return [

            Tables\Filters\SelectFilter::make('account_status')
                ->options([
                    'active' => 'Active',
                    'blocked' => 'Blocked',
                ]),

            Tables\Filters\TernaryFilter::make('is_active')
                ->label('Activation'),
        ];
    }

    // =========================
    // TABLE ACTIONS (BLOCK/UNBLOCK)
    // =========================
    protected function getTableActions(): array
    {
        return [

            Action::make('block')
                ->label('Block')
                ->color('danger')
                ->visible(fn ($record) => $record->account_status === 'active')
                ->requiresConfirmation()
              ->action(function ($record) {

    $record->update([
        'account_status' => 'blocked',
    ]);

    ActivityLogService::log(
        'Member Panel',
        'Member Blocked',
        'Blocked member: ' . $record->user_id . ' (' . $record->fullname . ')'
    );
}),

            Action::make('activate_account')
                ->label('Activate')
                ->color('success')
                ->visible(fn ($record) => $record->account_status === 'blocked')
                ->requiresConfirmation()
             ->action(function ($record) {

    $record->update([
        'account_status' => 'active',
    ]);

    ActivityLogService::log(
        'Member Panel',
        'Member Unblocked',
        'Unblocked member: ' . $record->user_id . ' (' . $record->fullname . ')'
    );
}),
        ];
    }

    // =========================
    // HEADER ACTION (ACTIVATE ID)
    // =========================
    protected function getHeaderActions(): array
    {
        return [

            Action::make('activate_id')
                ->label('Activate ID')
                ->icon('heroicon-o-bolt')
                ->color('primary')
                ->form([

                    TextInput::make('member_id')
                        ->label('Member ID')
                        ->required(),

                    Select::make('package')
                        ->label('Select Package')
                        ->options([
                            1 => 'P1 - 1 Step Pack',
                            2 => 'P2 - 2 Step Pack',
                            3 => 'P3 - 3 Step Pack',
                            4 => 'P4 - 4 Step Pack',
                            5 => 'P5 - 5 Step Pack',
                            6 => 'P6 - 6 Step Pack',
                        ])
                        ->required(),

                ])
                ->requiresConfirmation()
                ->action(function ($data) {

                    $member = Member::where('user_id', $data['member_id'])->first();

                    if (!$member) {
                        Notification::make()
                            ->title('Member not found')
                            ->danger()
                            ->send();
                        return;
                    }

                    if ($member->account_status === 'blocked') {
                        Notification::make()
                            ->title('Member is blocked')
                            ->danger()
                            ->send();
                        return;
                    }

                    $member->update([
                        'is_active' => 1,
                        'package_step' => $data['package'],
                        'pv' => $this->getPv($data['package']),
                    ]);
                    
                    ActivityLogService::log(
    'Member Panel',
    'Member Activated',
    'Activated member: ' . $member->user_id .
    ' (' . $member->fullname . ')' .
    ' | Package: P' . $data['package']
);

                    Notification::make()
                        ->title('Member Activated Successfully')
                        ->success()
                        ->send();
                }),
        ];
    }

    // =========================
    // PV LOGIC
    // =========================
    private function getPv($package)
    {
        return match ($package) {
            1 => 125,
            2 => 250,
            3 => 500,
            4 => 1000,
            5 => 2000,
            6 => 4000,
            default => 0,
        };
    }
    
    public static function canAccess(): bool
{
    $user = auth()->user();

    if (! $user) {
        return false;
    }

    if ($user->isOwner()) {
        return true;
    }

    return $user->hasPermission('active_block');
}

public static function shouldRegisterNavigation(): bool
{
    return static::canAccess();
}
}