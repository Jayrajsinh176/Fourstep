<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Orderecom;
use App\Models\Productecom;
use App\Models\Categoryecom;
use App\Models\Memberecom;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StatsOverview extends StatsOverviewWidget
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

    // Ecommerce-related permissions
    return
        $user->hasPermission('customers') ||
        $user->hasPermission('categories') ||
        $user->hasPermission('products.view') ||
        $user->hasPermission('ecommerce_orders.view');
}

    protected function getStats(): array
    {
        return [

        // ðŸ‘¥ Total Members
            Stat::make('Total Members', Memberecom::count())
                ->description('Registered users')
                ->color('info'),

            // ðŸ“¦ Total Orders
            Stat::make('Total Orders', Orderecom::count())
                ->description('All orders')
                ->color('primary'),

            // ðŸŸ¡ Today's Orders
            Stat::make("Today's Orders",
                Orderecom::whereDate('created_at', Carbon::today())->count()
            )
                ->description('Orders placed today')
                ->color('warning'),

            
            // ðŸ›’ Total Products
            Stat::make('Total Products', Productecom::count())
                ->description('All products')
                ->color('success'),

            // ðŸ—‚ Total Categories
            Stat::make('Total Categories', Categoryecom::count())
                ->description('All categories')
                ->color('primary'),

            // ðŸ’° Total Revenue
            Stat::make(
    'Total Revenue',
    '₹ ' . number_format(Orderecom::sum('total_amount'), 2)
)
                ->description('Total earnings')
                ->color('success'),

        ];
    }
}