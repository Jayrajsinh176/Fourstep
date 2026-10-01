@extends('pdf.layouts.report')

@section('title')
Royalty Club Bonus Report
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">{{ $reports->count() }}</div>
        </td>

        <td>
            <div class="summary-title">Total Monthly Turnover</div>
            <div class="summary-value">
                ₹ {{ number_format($totalTurnover, 2) }}
            </div>
        </td>

        <td>
            <div class="summary-title">Total Royalty Bonus</div>
            <div class="summary-value">
                ₹ {{ number_format($totalBonus, 2) }}
            </div>
        </td>

    </tr>

</table>

@endsection

@section('content')

<table class="data-table">

    <thead>

        <tr>
            <th>Sr.</th>
            <th>Member ID</th>
            <th>Member Name</th>
            <th>Bonus Month</th>
            <th>Turnover</th>
            <th>Pool %</th>
            <th>Royalty Pool</th>
            <th>Eligible Users</th>
            <th>Royalty Bonus</th>
            <th>Status</th>
        </tr>

    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">{{ $loop->iteration }}</td>

            <td>{{ $report->member->user_id ?? '-' }}</td>

            <td>{{ $report->member->fullname ?? '-' }}</td>

            <td class="center">{{ $report->month_key }}</td>

            <td class="right">
                ₹ {{ number_format($report->monthly_turnover,2) }}
            </td>

            <td class="center">
                {{ number_format($report->pool_percentage,2) }}%
            </td>

            <td class="right">
                ₹ {{ number_format($report->royalty_pool_amount,2) }}
            </td>

            <td class="center">
                {{ $report->eligible_users_count }}
            </td>

            <td class="right">
                ₹ {{ number_format($report->bonus_amount,2) }}
            </td>

            <td class="center">
                {{ ucfirst($report->status) }}
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="10" class="center">
                No records found.
            </td>
        </tr>

    @endforelse

    </tbody>

    <tfoot>

        <tr>

            <th colspan="4" class="right">
                Grand Total ({{ $reports->count() }} Record{{ $reports->count() > 1 ? 's' : '' }})
            </th>

            <th class="right">
                ₹ {{ number_format($totalTurnover,2) }}
            </th>

            <th></th>

            <th class="right">
                ₹ {{ number_format($totalPool,2) }}
            </th>

            <th></th>

            <th class="right">
                ₹ {{ number_format($totalBonus,2) }}
            </th>

            <th></th>

        </tr>

    </tfoot>

</table>

@endsection