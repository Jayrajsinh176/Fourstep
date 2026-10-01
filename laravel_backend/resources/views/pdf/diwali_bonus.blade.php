@extends('pdf.layouts.report')

@section('title')
Diwali Bonus Report
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">{{ $reports->count() }}</div>
        </td>

        <td>
            <div class="summary-title">Total Lapsed PV</div>
            <div class="summary-value">
                {{ number_format($totalLapsedPv, 2) }}
            </div>
        </td>

        <td>
            <div class="summary-title">Total Bonus</div>
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
            <th>Bonus Year</th>
            <th>Period Start</th>
            <th>Period End</th>
            <th>Lapsed PV</th>
            <th>Bonus %</th>
            <th>Bonus Amount</th>
        </tr>

    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">{{ $loop->iteration }}</td>

            <td>{{ $report->member->user_id ?? '-' }}</td>

            <td>{{ $report->member->fullname ?? '-' }}</td>

            <td class="center">{{ $report->bonus_year }}</td>

            <td class="center">
                {{ \Carbon\Carbon::parse($report->period_start)->format('d M Y') }}
            </td>

            <td class="center">
                {{ \Carbon\Carbon::parse($report->period_end)->format('d M Y') }}
            </td>

            <td class="right">
                {{ number_format($report->total_lapsed_pv, 2) }}
            </td>

            <td class="center">
                {{ number_format($report->bonus_percentage, 2) }}%
            </td>

            <td class="right">
                ₹ {{ number_format($report->bonus_amount, 2) }}
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="9" class="center">
                No records found.
            </td>
        </tr>

    @endforelse

    </tbody>

    <tfoot>

        <tr>

            <th colspan="6" class="right">
                Grand Total ({{ $reports->count() }} Record{{ $reports->count() > 1 ? 's' : '' }})
            </th>

            <th class="right">
                {{ number_format($totalLapsedPv, 2) }}
            </th>

            <th></th>

            <th class="right">
                ₹ {{ number_format($totalBonus, 2) }}
            </th>

        </tr>

    </tfoot>

</table>

@endsection