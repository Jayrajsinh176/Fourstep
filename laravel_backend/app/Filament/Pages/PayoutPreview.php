<?php

namespace App\Filament\Pages;

use App\Models\WeeklyClosing;
use App\Models\PayoutBatch;
use App\Models\PayoutDetail;
use App\Models\Wallet;
use App\Models\LoyaltyBonus;
use App\Models\Member;
use App\Models\BusinessMonitoringBonus;
use App\Models\RoyaltyClubBonus;
use App\Models\FamilySaverBonus;

use Illuminate\Support\Facades\DB;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class PayoutPreview extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'payout-preview';

    protected string $view = 'filament.pages.payout-preview';

    public ?string $weekStart = null;

    public ?string $weekEnd = null;

    public ?array $data = [];

    public int $totalMembers = 0;

    public float $totalAmount = 0;

    public function mount(): void
    {
        $this->weekStart = request()->query('week_start');

        $this->weekEnd = request()->query('week_end');

        abort_if(
            blank($this->weekStart) || blank($this->weekEnd),
            404,
            'Weekly closing period not found.'
        );

        $this->form->fill([
            'weekStart' => $this->weekStart,
            'weekEnd' => $this->weekEnd,
        ]);

        $this->refreshSummary();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Forms\Components\DatePicker::make('weekStart')
                    ->label('Week Start')
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->disabled()
                    ->required(),

                Forms\Components\DatePicker::make('weekEnd')
                    ->label('Week End')
                    ->displayFormat('d-m-Y')
                    ->native(false)
                    ->disabled()
                    ->required(),

            ])
            ->statePath('data');
    }

    private function getPayoutMemberIds()
{
    $binaryMemberIds = WeeklyClosing::query()
        ->whereDate('week_start', $this->weekStart)
        ->whereDate('week_end', $this->weekEnd)
        ->where('status', 'pending')
        ->where('payout_excluded', false)
        ->where('income', '>', 0)
        ->pluck('member_id');

$excludedMemberIds = WeeklyClosing::query()
    ->whereDate('week_start', $this->weekStart)
    ->whereDate('week_end', $this->weekEnd)
    ->where('payout_excluded', true)
    ->pluck('member_id');

$repurchaseMemberIds = LoyaltyBonus::query()
    ->whereDate('week_start', $this->weekStart)
    ->whereDate('week_end', $this->weekEnd)
    ->where('type', 'repurchase')
    ->where('status', 'approved')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('member_id', $excludedMemberIds)
    ->pluck('member_id');

$businessMonitoringMemberIds = BusinessMonitoringBonus::query()
    ->whereDate('cycle_date', $this->weekStart)
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('sponsor_member_id', $excludedMemberIds)
    ->pluck('sponsor_member_id');

    $royaltyMemberIds = RoyaltyClubBonus::query()
    ->where('month_key', '<=', now()->format('Y-m'))
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('member_id', $excludedMemberIds)
    ->pluck('member_id');

$familySaverMemberIds = FamilySaverBonus::query()
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('nominee_member_id', $excludedMemberIds)
    ->pluck('nominee_member_id');

return $binaryMemberIds
    ->merge($repurchaseMemberIds)
    ->merge($businessMonitoringMemberIds)
    ->merge($royaltyMemberIds)
    ->merge($familySaverMemberIds)
    ->unique()
    ->values();
}

private function getRepurchaseBonus(int $memberId): float
{
    return (float) LoyaltyBonus::query()
        ->where('member_id', $memberId)
        ->whereDate('week_start', $this->weekStart)
        ->whereDate('week_end', $this->weekEnd)
        ->where('type', 'repurchase')
        ->where('status', 'approved')
        ->sum('bonus_amount');
}

private function getBusinessMonitoringBonus(int $memberId): float
{
    return (float) BusinessMonitoringBonus::query()
        ->where('sponsor_member_id', $memberId)
        ->whereDate('cycle_date', $this->weekStart)
        ->where('status', 'pending')
        ->where('bonus_amount', '>', 0)
        ->sum('bonus_amount');
}

private function getRoyaltyBonus(int $memberId): float
{
    return (float) RoyaltyClubBonus::query()
        ->where('member_id', $memberId)
        ->where('month_key', '<=', now()->format('Y-m'))
        ->where('status', 'pending')
        ->where('bonus_amount', '>', 0)
        ->sum('bonus_amount');
}

private function getFamilySaverBonus(int $memberId): float
{
    return (float) FamilySaverBonus::query()
   ->where('nominee_member_id', $memberId)
        ->where('status', 'pending')
        ->where('bonus_amount', '>', 0)
        ->sum('bonus_amount');
}

private function getBinaryBonus(int $memberId): float
{
    return (float) WeeklyClosing::query()
        ->where('member_id', $memberId)
        ->whereDate('week_start', $this->weekStart)
        ->whereDate('week_end', $this->weekEnd)
        ->where('status', 'pending')
        ->where('payout_excluded', false)
        ->where('income', '>', 0)
        ->sum('income');
}

private function isMemberExcluded(int $memberId): bool
{
    return WeeklyClosing::query()
        ->where('member_id', $memberId)
        ->whereDate('week_start', $this->weekStart)
        ->whereDate('week_end', $this->weekEnd)
        ->where('payout_excluded', true)
        ->exists();
}

private function getExclusionReason(int $memberId): ?string
{
    return WeeklyClosing::query()
        ->where('member_id', $memberId)
        ->whereDate('week_start', $this->weekStart)
        ->whereDate('week_end', $this->weekEnd)
        ->where('payout_excluded', true)
        ->value('payout_excluded_reason');
}

 public function table(Table $table): Table
{
    $memberIds = $this->getPayoutMemberIds();

    return $table
        ->query(
            Member::query()
                ->whereIn('id', $memberIds)
                ->where('status', 1)
        )
        ->columns([

            Tables\Columns\TextColumn::make('user_id')
                ->label('Member ID')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('fullname')
                ->label('Name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('pan_no')
                ->label('PAN'),

            Tables\Columns\TextColumn::make('mobile_no')
                ->label('Mobile'),

            Tables\Columns\TextColumn::make('binary_bonus')
                ->label('Binary Bonus')
                ->state(fn ($record) =>
                    $this->getBinaryBonus($record->id)
                )
                ->money('INR'),

            Tables\Columns\TextColumn::make('repurchase_bonus')
                ->label('Repurchase Bonus')
                ->state(fn ($record) =>
                    $this->getRepurchaseBonus($record->id)
                )
                ->money('INR'),

                Tables\Columns\TextColumn::make('business_monitoring_bonus')
    ->label('Business Monitoring')
    ->state(fn ($record) =>
        $this->getBusinessMonitoringBonus($record->id)
    )
    ->money('INR'),

    Tables\Columns\TextColumn::make('royalty_bonus')
    ->label('Royalty Club')
    ->state(fn ($record) =>
        $this->getRoyaltyBonus($record->id)
    )
    ->money('INR'),

    Tables\Columns\TextColumn::make('family_saver_bonus')
    ->label('Family Saver')
    ->state(fn ($record) =>
        $this->getFamilySaverBonus($record->id)
    )
    ->money('INR'),


          Tables\Columns\TextColumn::make('gross_amount')
    ->label('Gross Amount')
    ->state(function ($record) {

        $binary = $this->getBinaryBonus($record->id);

        $repurchase = $this->getRepurchaseBonus($record->id);

        $businessMonitoring = $this->getBusinessMonitoringBonus($record->id);

   $royalty = $this->getRoyaltyBonus($record->id);

$familySaver = $this->getFamilySaverBonus($record->id);

return $binary
    + $repurchase
    + $businessMonitoring
    + $royalty
    + $familySaver;
    })
    ->money('INR'),

       Tables\Columns\TextColumn::make('tds_preview')
    ->label('TDS')
    ->state(function ($record) {

        $binary = $this->getBinaryBonus($record->id);

        $repurchase = $this->getRepurchaseBonus($record->id);

        $businessMonitoring = $this->getBusinessMonitoringBonus($record->id);

        $royalty = $this->getRoyaltyBonus($record->id);

$familySaver = $this->getFamilySaverBonus($record->id);

$gross =
    $binary
    + $repurchase
    + $businessMonitoring
    + $royalty
    + $familySaver;

return round($gross * 5 / 100, 2);

    })
    ->money('INR'),

    Tables\Columns\TextColumn::make('admin_charge_preview')
    ->label('Admin Charge')
    ->state(function ($record) {

        $binary = $this->getBinaryBonus($record->id);

        $repurchase = $this->getRepurchaseBonus($record->id);

        $businessMonitoring = $this->getBusinessMonitoringBonus($record->id);

        $royalty = $this->getRoyaltyBonus($record->id);

        $familySaver = $this->getFamilySaverBonus($record->id);

        $gross =
            $binary
            + $repurchase
            + $businessMonitoring
            + $royalty
            + $familySaver;

        return round($gross * 10 / 100, 2);
    })
    ->money('INR'),

Tables\Columns\TextColumn::make('net_amount_preview')
    ->label('Net Amount')
    ->state(function ($record) {

        $binary = $this->getBinaryBonus($record->id);

        $repurchase = $this->getRepurchaseBonus($record->id);

        $businessMonitoring = $this->getBusinessMonitoringBonus($record->id);

        $royalty = $this->getRoyaltyBonus($record->id);

        $familySaver = $this->getFamilySaverBonus($record->id);

        $gross =
            $binary
            + $repurchase
            + $businessMonitoring
            + $royalty
            + $familySaver;

        $tds = round($gross * 5 / 100, 2);

        $adminCharge = round($gross * 10 / 100, 2);

        return round(
            $gross - $tds - $adminCharge,
            2
        );
    })
    ->money('INR'),

          Tables\Columns\TextColumn::make('payout_status')
    ->label('Status')
    ->state(function ($record) {

        $binary = $this->getBinaryBonus($record->id);

        $repurchase = $this->getRepurchaseBonus($record->id);

        $businessMonitoring = $this->getBusinessMonitoringBonus($record->id);

        $royalty = $this->getRoyaltyBonus($record->id);

        $familySaver = $this->getFamilySaverBonus($record->id);

        return (
            $binary > 0 ||
            $repurchase > 0 ||
            $businessMonitoring > 0 ||
            $royalty > 0 ||
            $familySaver > 0
        )
            ? 'Pending'
            : '—';
    })
    ->badge()
    ->color('warning'),

                Tables\Columns\TextColumn::make('payout_excluded')
    ->label('Payout Status')
    ->state(fn ($record) =>
        $this->isMemberExcluded($record->id)
            ? 'Excluded'
            : 'Included'
    )
    ->badge()
    ->color(fn ($record) =>
        $this->isMemberExcluded($record->id)
            ? 'danger'
            : 'success'
    ),

Tables\Columns\TextColumn::make('payout_excluded_reason')
    ->label('Exclusion Reason')
    ->state(fn ($record) =>
        $this->getExclusionReason($record->id)
    )
    ->placeholder('-')
    ->wrap()
    ->toggleable(),


        ])
->actions([

    Action::make('exclude')
        ->label('Exclude')
        ->icon('heroicon-o-x-circle')
        ->color('danger')
        ->visible(fn ($record) =>
            ! $this->isMemberExcluded($record->id)
        )
        ->requiresConfirmation()
        ->form([
            Forms\Components\Textarea::make('reason')
                ->label('Reason for Excluding')
                ->required(),
        ])
        ->action(function ($record, array $data) {

            WeeklyClosing::query()
                ->where('member_id', $record->id)
                ->whereDate('week_start', $this->weekStart)
                ->whereDate('week_end', $this->weekEnd)
                ->where('status', 'pending')
                ->update([
                    'payout_excluded' => true,
                    'payout_excluded_reason' => $data['reason'],
                    'payout_excluded_by' => auth()->id(),
                    'payout_excluded_at' => now(),
                    'updated_at' => now(),
                ]);

            Notification::make()
                ->title('Payout excluded successfully.')
                ->success()
                ->send();

            $this->resetTable();
            $this->refreshSummary();
        }),

    Action::make('include')
        ->label('Include')
        ->icon('heroicon-o-check-circle')
        ->color('success')
        ->visible(fn ($record) =>
            $this->isMemberExcluded($record->id)
        )
        ->requiresConfirmation()
        ->action(function ($record) {

            WeeklyClosing::query()
                ->where('member_id', $record->id)
                ->whereDate('week_start', $this->weekStart)
                ->whereDate('week_end', $this->weekEnd)
                ->where('status', 'pending')
                ->update([
                    'payout_excluded' => false,
                    'payout_excluded_reason' => null,
                    'payout_excluded_by' => null,
                    'payout_excluded_at' => null,
                    'updated_at' => now(),
                ]);

            Notification::make()
                ->title('Payout included successfully.')
                ->success()
                ->send();

            $this->resetTable();
            $this->refreshSummary();
        }),

])
->defaultPaginationPageOption(10);
}

    protected function getHeaderActions(): array
    {
        return [

            Action::make('processPayout')
                ->label('Process Payout')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Process Weekly Payout')
                ->modalDescription(
                    'Are you sure you want to process this weekly payout? This action cannot be undone.'
                )
                ->modalSubmitActionLabel('Process')
                ->action('processPayout'),

        ];
    }

 protected function refreshSummary(): void
{
    $memberIds = $this->getPayoutMemberIds();

    $this->totalMembers = $memberIds->count();

    $binaryTotal = (float) WeeklyClosing::query()
        ->whereDate('week_start', $this->weekStart)
        ->whereDate('week_end', $this->weekEnd)
        ->where('status', 'pending')
        ->where('income', '>', 0)
        ->where('payout_excluded', false)
        ->sum('income');

 $excludedMemberIds = WeeklyClosing::query()
    ->whereDate('week_start', $this->weekStart)
    ->whereDate('week_end', $this->weekEnd)
    ->where('payout_excluded', true)
    ->pluck('member_id');

$repurchaseTotal = (float) LoyaltyBonus::query()
    ->whereDate('week_start', $this->weekStart)
    ->whereDate('week_end', $this->weekEnd)
    ->where('type', 'repurchase')
    ->where('status', 'approved')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('member_id', $excludedMemberIds)
    ->sum('bonus_amount');

$businessMonitoringTotal = (float) BusinessMonitoringBonus::query()
    ->whereDate('cycle_date', $this->weekStart)
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('sponsor_member_id', $excludedMemberIds)
    ->sum('bonus_amount');

$royaltyTotal = (float) RoyaltyClubBonus::query()
    ->where('month_key', '<=', now()->format('Y-m'))
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('member_id', $excludedMemberIds)
    ->sum('bonus_amount');

$familySaverTotal = (float) FamilySaverBonus::query()
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('member_id', $excludedMemberIds)
    ->sum('bonus_amount');

$this->totalAmount =
    $binaryTotal
    + $repurchaseTotal
    + $businessMonitoringTotal
    + $royaltyTotal
    + $familySaverTotal;
}

public function processPayout(): void
{
    $memberIds = $this->getPayoutMemberIds();

    if ($memberIds->isEmpty()) {

        Notification::make()
            ->title('No pending payout found.')
            ->danger()
            ->send();

        return;
    }

    DB::beginTransaction();

    try {

/*
|--------------------------------------------------------------------------
| Calculate Total Payout
|--------------------------------------------------------------------------
*/

$binaryTotal = (float) WeeklyClosing::query()
    ->whereDate('week_start', $this->weekStart)
    ->whereDate('week_end', $this->weekEnd)
    ->where('status', 'pending')
    ->where('payout_excluded', false)
    ->where('income', '>', 0)
    ->sum('income');

    $excludedMemberIds = WeeklyClosing::query()
    ->whereDate('week_start', $this->weekStart)
    ->whereDate('week_end', $this->weekEnd)
    ->where('payout_excluded', true)
    ->pluck('member_id');


$repurchaseTotal = (float) LoyaltyBonus::query()
    ->whereDate('week_start', $this->weekStart)
    ->whereDate('week_end', $this->weekEnd)
    ->where('type', 'repurchase')
    ->where('status', 'approved')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('member_id', $excludedMemberIds)
    ->sum('bonus_amount');

$businessMonitoringTotal = (float) BusinessMonitoringBonus::query()
    ->whereDate('cycle_date', $this->weekStart)
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('sponsor_member_id', $excludedMemberIds)
    ->sum('bonus_amount');

$royaltyTotal = (float) RoyaltyClubBonus::query()
    ->where('month_key', '<=', now()->format('Y-m'))
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('member_id', $excludedMemberIds)
    ->sum('bonus_amount');

$familySaverTotal = (float) FamilySaverBonus::query()
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->whereNotIn('member_id', $excludedMemberIds)
    ->sum('bonus_amount');

$grandTotal =
    $binaryTotal
    + $repurchaseTotal
    + $businessMonitoringTotal
    + $royaltyTotal
    + $familySaverTotal;

        /*
        |--------------------------------------------------------------------------
        | Create Payout Batch
        |--------------------------------------------------------------------------
        */

        $batch = PayoutBatch::create([
            'batch_no' => 'PAY' . now()->format('YmdHis'),
            'payout_date' => $this->weekEnd,
            'total_members' => $memberIds->count(),
            'total_amount' => $grandTotal,
            'status' => 'pending',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Payout Detail Per Member
        |--------------------------------------------------------------------------
        */

        foreach ($memberIds as $memberId) {

            $member = Member::find($memberId);

            if (! $member) {
                continue;
            }

$binaryBonus = $this->getBinaryBonus($memberId);

$repurchaseBonus = $this->getRepurchaseBonus($memberId);

$businessMonitoringBonus = $this->getBusinessMonitoringBonus($memberId);

$royaltyBonus = $this->getRoyaltyBonus($memberId);

$familySaverBonus = $this->getFamilySaverBonus($memberId);

$grossAmount =
    $binaryBonus
    + $repurchaseBonus
    + $businessMonitoringBonus
    + $royaltyBonus
    + $familySaverBonus;

            if ($grossAmount <= 0) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | TDS / Admin Charge
            |--------------------------------------------------------------------------
            */

            $tds = round($grossAmount * 5 / 100, 2);

            $adminCharge = round($grossAmount * 10 / 100, 2);

            $netAmount = round(
                $grossAmount - $tds - $adminCharge,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Payout Detail
            |--------------------------------------------------------------------------
            */

            PayoutDetail::create([

                'batch_id' => $batch->id,

                'member_id' => $member->user_id,

                'repurchase_bonus' => $repurchaseBonus,

                'leadership_bonus' => 0,

                'cashback_bonus' => 0,

                'binary_bonus' => $binaryBonus,

                'group_buildup_bonus' => 0,

           'royalty_bonus' => $royaltyBonus,

                'consistency_bonus' => 0,

'business_monitoring_bonus' => $businessMonitoringBonus,

                'rank_reward_bonus' => 0,

                'branch_turnover_bonus' => 0,

'family_saver_bonus' => $familySaverBonus,

                'total_amount' => $grossAmount,

                'gross_amount' => $grossAmount,

                'tds' => $tds,

                'admin_charge' => $adminCharge,

                'net_amount' => $netAmount,

                'reference_no' => null,

                'transaction_no' => null,

                'cheque_no' => null,

                'paid_at' => null,

                'payment_status' => 'pending',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Wallet
            |--------------------------------------------------------------------------
            */

           $wallet = Wallet::firstOrCreate(
    [
        'user_id' => $memberId,
    ],
    [
        'matching_income' => 0,
        'repurchase_income' => 0,
        'royalty_income' => 0,
        'family_saver_income' => 0,
        'total_income' => 0,
    ]
);

/*
|--------------------------------------------------------------------------
| Credit Income According To Bonus Type
|--------------------------------------------------------------------------
| Allocate the NET payout proportionally between all bonus types.
| This guarantees wallet category credits never exceed total_income.
|--------------------------------------------------------------------------
*/

$repurchaseNet = 0;
$businessMonitoringNet = 0;
$binaryNet = 0;
$royaltyNet = 0;
$familySaverNet = 0;

if ($grossAmount > 0 && $netAmount > 0) {

    if ($binaryBonus > 0) {
        $binaryNet = round(
            ($binaryBonus / $grossAmount) * $netAmount,
            2
        );
    }

    if ($repurchaseBonus > 0) {
        $repurchaseNet = round(
            ($repurchaseBonus / $grossAmount) * $netAmount,
            2
        );
    }

    if ($businessMonitoringBonus > 0) {
        $businessMonitoringNet = round(
            ($businessMonitoringBonus / $grossAmount) * $netAmount,
            2
        );
    }

    if ($royaltyBonus > 0) {
    $royaltyNet = round(
        ($royaltyBonus / $grossAmount) * $netAmount,
        2
    );
    }

    if ($familySaverBonus > 0) {
    $familySaverNet = round(
        ($familySaverBonus / $grossAmount) * $netAmount,
        2
    );


    
}

    /*
    |--------------------------------------------------------------------------
    | Rounding Adjustment
    |--------------------------------------------------------------------------
    | Make sure category totals exactly equal the net payout.
    |--------------------------------------------------------------------------
    */

    $allocatedNet =
    $binaryNet
    + $repurchaseNet
    + $businessMonitoringNet
    + $royaltyNet
    + $familySaverNet;

    $difference = round(
        $netAmount - $allocatedNet,
        2
    );

    if ($difference != 0) {
        $binaryNet = round(
            $binaryNet + $difference,
            2
        );
    }
}

/*
|--------------------------------------------------------------------------
| Binary + Business Monitoring
| Both are existing matching income categories.
|--------------------------------------------------------------------------
*/

$matchingIncomeNet =
    $binaryNet
    + $businessMonitoringNet;

if ($matchingIncomeNet > 0) {

    $wallet->increment(
        'matching_income',
        $matchingIncomeNet
    );
}

/*
|--------------------------------------------------------------------------
| Repurchase Income
|--------------------------------------------------------------------------
*/

if ($repurchaseNet > 0) {

    $wallet->increment(
        'repurchase_income',
        $repurchaseNet
    );
}

/*
|--------------------------------------------------------------------------
| Royalty Club Income
|--------------------------------------------------------------------------
*/

if ($royaltyNet > 0) {

    $wallet->increment(
        'royalty_income',
        $royaltyNet
    );
}

/*
|--------------------------------------------------------------------------
| Family Saver Income
|--------------------------------------------------------------------------
*/

if ($familySaverNet > 0) {

    $wallet->increment(
        'family_saver_income',
        $familySaverNet
    );
}

/*
|--------------------------------------------------------------------------
| Credit Spendable Repurchase Wallet
|--------------------------------------------------------------------------
*/

if ($repurchaseNet > 0) {

    $lastRepurchaseBalance = DB::table('repurchase_wallet_transactions')
        ->where('user_id', $memberId)
        ->orderByDesc('id')
        ->value('balance_after') ?? 0;

    $alreadyCredited = DB::table('repurchase_wallet_transactions')
        ->where('user_id', $memberId)
        ->where(
            'description',
            'Repurchase Payout #' . $batch->id
        )
        ->exists();

    if (! $alreadyCredited) {

        DB::table('repurchase_wallet_transactions')->insert([
            'user_id' => $memberId,
            'type' => 'credit',
            'amount' => $repurchaseNet,
            'description' => 'Repurchase Payout #' . $batch->id,
            'balance_after' => $lastRepurchaseBalance + $repurchaseNet,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}


$wallet->increment(
    'total_income',
    $netAmount
);

            /*
            |--------------------------------------------------------------------------
            | Mark Binary Closing Paid
            |--------------------------------------------------------------------------
            */

            WeeklyClosing::query()
                ->where('member_id', $memberId)
                ->whereDate('week_start', $this->weekStart)
                ->whereDate('week_end', $this->weekEnd)
                ->where('status', 'pending')
                ->update([
                    'status' => 'paid',
                    'updated_at' => now(),
                ]);

            /*
            |--------------------------------------------------------------------------
            | Mark Repurchase Paid
            |--------------------------------------------------------------------------
            */

            LoyaltyBonus::query()
                ->where('member_id', $memberId)
                ->whereDate('week_start', $this->weekStart)
                ->whereDate('week_end', $this->weekEnd)
                ->where('type', 'repurchase')
                ->where('status', 'approved')
                ->update([
                    'status' => 'paid',
                    'updated_at' => now(),
                ]);
                BusinessMonitoringBonus::query()
    ->where('sponsor_member_id', $memberId)
    ->whereDate('cycle_date', $this->weekStart)
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->update([
        'status' => 'paid',
        'updated_at' => now(),
    ]);

    /*
|--------------------------------------------------------------------------
| Mark Royalty Club Paid
|--------------------------------------------------------------------------
*/

RoyaltyClubBonus::query()
    ->where('member_id', $memberId)
    ->where('month_key', '<=', now()->format('Y-m'))
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->update([
        'status' => 'paid',
        'updated_at' => now(),
    ]);

    /*
|--------------------------------------------------------------------------
| Mark Family Saver Paid
|--------------------------------------------------------------------------
*/

FamilySaverBonus::query()
   ->where('nominee_member_id', $memberId)
    ->where('status', 'pending')
    ->where('bonus_amount', '>', 0)
    ->update([
        'status' => 'paid',
        'updated_at' => now(),
    ]);
    
        }

        DB::commit();

        Notification::make()
            ->title('Payout Details Created Successfully')
            ->body(
                'Batch: ' . $batch->batch_no .
                ' | Members: ' . $memberIds->count() .
                ' | Amount: ₹' .
                number_format($grandTotal, 2)
            )
            ->success()
            ->send();

        $this->resetTable();

        $this->refreshSummary();

    } catch (\Throwable $e) {

        DB::rollBack();

        Notification::make()
            ->title('Payout Processing Failed')
            ->body($e->getMessage())
            ->danger()
            ->send();
    }
}

}