<?php

namespace App\Filament\Resources\CapitalTransactions\Widgets;

use App\Models\CapitalTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CapitalTransactionStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalCredit = CapitalTransaction::where('category', 'capital')
            ->where('transaction_type', 'credit')
            ->sum('amount');

        $totalDebit = CapitalTransaction::where('category', 'capital')
            ->where('transaction_type', 'debit')
            ->sum('amount');

        $availableCapital = $totalCredit - $totalDebit;

        return [
            Stat::make(
                'Total Capital Credit',
                '₹ ' . number_format($totalCredit, 2)
            )
                ->description('Total capital added')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success'),

            Stat::make(
                'Total Capital Debit',
                '₹ ' . number_format($totalDebit, 2)
            )
                ->description('Total capital deducted')
                ->descriptionIcon('heroicon-o-arrow-trending-down')
                ->color('danger'),

            Stat::make(
                'Available Capital',
                '₹ ' . number_format($availableCapital, 2)
            )
                ->description('Capital Credit - Capital Debit')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('primary'),
        ];
    }
}