<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use Illuminate\Support\Facades\Auth;

use App\Models\Shoppee_Member;
use App\Models\Shoppee_Kyc;
use App\Models\Shoppee_ProductRequest;
use App\Models\Shoppee_BalanceRequest;
use App\Models\Shoppee_Order;

class ShoppeeStatsOverview extends StatsOverviewWidget
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

        return
            $user->hasPermission('shoppee_members.view') ||
            $user->hasPermission('shoppee_kyc.view') ||
            $user->hasPermission('shoppee_product_requests.view') ||
            $user->hasPermission('shoppee_balance_requests.view') ||
            $user->hasPermission('shoppee_orders.view');
    }

    protected function getStats(): array
    {
        return [

            Stat::make(
                'Total Members',
                Shoppee_Member::count()
            )
                ->description('Registered Shoppee Members')
                ->color('primary'),

            Stat::make(
                'Pending KYC',
                Shoppee_Kyc::where('status', 'pending')->count()
            )
                ->description('Awaiting Approval')
                ->color('warning'),

            Stat::make(
                'Pending Product Requests',
                Shoppee_ProductRequest::where('status', 'Pending')->count()
            )
                ->description('Waiting for Approval')
                ->color('info'),

            Stat::make(
                'Pending Balance Requests',
                Shoppee_BalanceRequest::where('status', 'pending')->count()
            )
                ->description('Waiting for Approval')
                ->color('danger'),

            Stat::make(
                'Total Orders',
                Shoppee_Order::count()
            )
                ->description('Total Shoppee Orders')
                ->color('success'),

        ];
    }
}