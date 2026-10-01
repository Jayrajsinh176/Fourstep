<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MemberStatsOverview extends StatsOverviewWidget
{
    
    public static function canView(): bool
{
    $user = Auth::user();

    if (! $user) {
        return false;
    }

    // Owner always sees it
    if ($user->isOwner()) {
        return true;
    }

    // Member-related permissions
    return
        $user->hasPermission('members.view') ||
        $user->hasPermission('member_kyc.view') ||
        $user->hasPermission('member_balance_requests.view') ||
        $user->hasPermission('reward_achievers') ||
        $user->hasPermission('rank_achievers') ||
        $user->hasPermission('daily_closing') ||
        $user->hasPermission('active_block');
}
    
    protected function getStats(): array
    {
        return [

            // 👥 Total Members
            Stat::make('Total Members', Member::count())
                ->description('All registered Members')
                ->color('primary'),

            // 🆕 Today's Members
            Stat::make("Today's Members",
                Member::whereDate('created_at', Carbon::today())->count()
            )
                ->description('New registrations today')
                ->color('success'),

            // 📅 This Month Members
            Stat::make("This Month",
                Member::whereMonth('created_at', Carbon::now()->month)->count()
            )
                ->description('Monthly registrations')
                ->color('info'),

            // 🟢 Active Members (example: status = 1)
            Stat::make('Active Members',
                Member::where('status', 1)->count()
            )
                ->description('Currently active Members')
                ->color('success'),

            // 🔴 Inactive Members
            Stat::make('Inactive Members',
                Member::where('status', 0)->count()
            )
                ->description('Inactive Members')
                ->color('danger'),

        ];
    }
}