@extends('pdf.layouts.report')

@section('title')
Business Monitoring Bonus Report
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">{{ $reports->count() }}</div>
        </td>

        <td>
            <div class="summary-title">Total Matching Income</div>
            <div class="summary-value">
                ₹ {{ number_format($totalMatchingIncome, 2) }}
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
            <th>Sponsor ID</th>
            <th>Sponsor Name</th>
            <th>Downline ID</th>
            <th>Downline Name</th>
            <th>Cycle Date</th>
            <th>Matching Income</th>
            <th>Bonus %</th>
            <th>Bonus Amount</th>
            <th>Status</th>
        </tr>

    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">{{ $loop->iteration }}</td>

            <td>{{ $report->sponsor->user_id ?? '-' }}</td>

            <td>{{ $report->sponsor->fullname ?? '-' }}</td>

            <td>{{ $report->downline->user_id ?? '-' }}</td>

            <td>{{ $report->downline->fullname ?? '-' }}</td>

            <td class="center">
                {{ \Carbon\Carbon::parse($report->cycle_date)->format('d M Y') }}
            </td>

            <td class="right">
                ₹ {{ number_format($report->matching_income, 2) }}
            </td>

            <td class="center">
                {{ number_format($report->bonus_percentage, 2) }}%
            </td>

            <td class="right">
                ₹ {{ number_format($report->bonus_amount, 2) }}
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

            <th colspan="6" class="right">
                Grand Total ({{ $reports->count() }} Record{{ $reports->count() > 1 ? 's' : '' }})
            </th>

            <th class="right">
                ₹ {{ number_format($totalMatchingIncome, 2) }}
            </th>

            <th></th>

            <th class="right">
                ₹ {{ number_format($totalBonus, 2) }}
            </th>

            <th></th>

        </tr>

    </tfoot>

</table>

@endsection