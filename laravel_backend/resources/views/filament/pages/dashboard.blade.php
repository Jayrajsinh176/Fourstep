<x-filament-panels::page>

    <style>
        .dashboard-wrapper {
            display: flex;
            flex-direction: column;
            gap: 50px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
        }

        .dashboard-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 2px 6px rgba(0,0,0,.05);
        }
    </style>

    <div class="dashboard-wrapper">

        {{-- Ecommerce Dashboard --}}
        @if(\App\Filament\Widgets\StatsOverview::canView())
            <div>
                <h2 class="section-title">Ecommerce Dashboard</h2>

                <div class="dashboard-card">
                    @livewire(\App\Filament\Widgets\StatsOverview::class)
                </div>
            </div>
        @endif


        {{-- Member Dashboard --}}
        @if(\App\Filament\Widgets\MemberStatsOverview::canView())
            <div>
                <h2 class="section-title">Member Dashboard</h2>

                <div class="dashboard-card">
                    @livewire(\App\Filament\Widgets\MemberStatsOverview::class)
                </div>
            </div>
        @endif


        {{-- Shoppee Dashboard --}}
        @if(\App\Filament\Widgets\ShoppeeStatsOverview::canView())
            <div>
                <h2 class="section-title">Shoppee Dashboard</h2>

                <div class="dashboard-card">
                    @livewire(\App\Filament\Widgets\ShoppeeStatsOverview::class)
                </div>
            </div>
        @endif

 {{-- Income Report  Dashboard --}}
@if(\App\Filament\Widgets\IncomeStatsOverview::canView())

<div>
    <h2 class="section-title">
        Income Reports Dashboard
    </h2>

    <div class="dashboard-card">
        @livewire(\App\Filament\Widgets\IncomeStatsOverview::class)
    </div>
</div>

@endif


        {{-- Recent Orders --}}
        @if(\App\Filament\Widgets\RecentOrders::canView())
            <div>
                <div class="dashboard-card">
                    @livewire(\App\Filament\Widgets\RecentOrders::class)
                </div>
            </div>
        @endif

    </div>

</x-filament-panels::page>