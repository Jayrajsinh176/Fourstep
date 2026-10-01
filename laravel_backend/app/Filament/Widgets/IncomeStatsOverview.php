<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

use App\Models\LoyaltyBonus;
use App\Models\LeadershipRankBonus;
use App\Models\CashbackWallet;

use App\Models\GroupBuiltupBonus;
use App\Models\RoyaltyClubBonus;
use App\Models\ConsistencyWallet;
use App\Models\BusinessMonitoringBonus;
use App\Models\RewardAchiever;
use App\Models\BranchTurnoverBonus;
use App\Models\FamilySaverBonus;

class IncomeStatsOverview extends StatsOverviewWidget
{
    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->isOwner()) {
            return true;
        }

        return
            $user->hasPermission('repurchase_bonus_report') ||
            $user->hasPermission('leadership_rank_report') ||
            $user->hasPermission('cashback_report') ||
            $user->hasPermission('group_builtup_bonus_report') ||
            $user->hasPermission('royalty_club_bonus_report') ||
            $user->hasPermission('consistency_bonus_report') ||
            $user->hasPermission('business_monitoring_report') ||
            $user->hasPermission('rank_reward_report') ||
            $user->hasPermission('branch_turnover_bonus_report') ||
            $user->hasPermission('family_saver_bonus_report');
    }
protected function getStats(): array
{
    $user = Auth::user();

    $stats = [];

    if ($user->hasPermission('repurchase_bonus_report')) {

        $stats[] = Stat::make(
            'Repurchase Bonus',
            '₹ ' . number_format(LoyaltyBonus::sum('bonus_amount'), 2)
        )
            ->description(
                LoyaltyBonus::count() . ' Beneficiaries'
            )
            ->color('success');

    }

    if ($user->hasPermission('leadership_rank_report')) {

        $stats[] = Stat::make(
            'Leadership Bonus',
            '₹ ' . number_format(LeadershipRankBonus::sum('bonus_amount'), 2)
        )
            ->description(
                LeadershipRankBonus::count() . ' Beneficiaries'
            )
            ->color('primary');

    }

    if ($user->hasPermission('cashback_report')) {

        $stats[] = Stat::make(
            'Cashback',
            '₹ ' . number_format(CashbackWallet::sum('credit'), 2)
        )
            ->description(
                CashbackWallet::where('credit', '>', 0)->count() . ' Transactions'
            )
            ->color('warning');

    }

    if ($user->hasPermission('group_builtup_bonus_report')) {

        $stats[] = Stat::make(
            'Group Built-up',
            '₹ ' . number_format(\App\Models\GroupBuiltupBonus::sum('payable_income'), 2)
        )
            ->description(
                \App\Models\GroupBuiltupBonus::count() . ' Beneficiaries'
            )
            ->color('success');

    }

    if ($user->hasPermission('royalty_club_bonus_report')) {

        $stats[] = Stat::make(
            'Royalty Club',
            '₹ ' . number_format(\App\Models\RoyaltyClubBonus::sum('bonus_amount'), 2)
        )
            ->description(
                \App\Models\RoyaltyClubBonus::count() . ' Beneficiaries'
            )
            ->color('warning');

    }

    if ($user->hasPermission('consistency_bonus_report')) {

        $stats[] = Stat::make(
            'Consistency Bonus',
            '₹ ' . number_format(\App\Models\ConsistencyWallet::sum('credit'), 2)
        )
            ->description(
                \App\Models\ConsistencyWallet::where('credit', '>', 0)->count() . ' Transactions'
            )
            ->color('primary');

    }

    if ($user->hasPermission('business_monitoring_report')) {

        $stats[] = Stat::make(
            'Business Monitoring',
            '₹ ' . number_format(\App\Models\BusinessMonitoringBonus::sum('bonus_amount'), 2)
        )
            ->description(
                \App\Models\BusinessMonitoringBonus::count() . ' Beneficiaries'
            )
            ->color('success');

    }

    if ($user->hasPermission('rank_reward_report')) {

        $stats[] = Stat::make(
            'Rank Reward',
            \App\Models\RewardAchiever::count()
        )
            ->description('Rewards Achieved')
            ->color('warning');

    }

    if ($user->hasPermission('branch_turnover_bonus_report')) {

        $stats[] = Stat::make(
            'Branch Turnover',
            '₹ ' . number_format(\App\Models\BranchTurnoverBonus::sum('bonus_amount'), 2)
        )
            ->description(
                \App\Models\BranchTurnoverBonus::count() . ' Branches'
            )
            ->color('success');

    }

    if ($user->hasPermission('family_saver_bonus_report')) {

        $stats[] = Stat::make(
            'Family Saver',
            '₹ ' . number_format(\App\Models\FamilySaverBonus::sum('bonus_amount'), 2)
        )
            ->description(
                \App\Models\FamilySaverBonus::count() . ' Beneficiaries'
            )
            ->color('danger');

    }

    return $stats;
}

}