@extends('pdf.layouts.report')

@section('title')
Purchase Activation Bonus Report
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">{{ $reports->count() }}</div>
        </td>

        <td>
            <div class="summary-title">Total Matching BV</div>
            <div class="summary-value">
                {{ number_format($totalMatchingBV, 2) }}
            </div>
        </td>

        <td>
            <div class="summary-title">Total Payable Bonus</div>
            <div class="summary-value">
                ₹ {{ number_format($totalPayableIncome, 2) }}
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
            <th>Week</th>
            <th>Step</th>
            <th>Left BV</th>
<th>Right BV</th>
            <th>Matching BV</th>
            <th>Pairs</th>
            <th>Income / Pair</th>
            <th>Payable Bonus</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">{{ $loop->iteration }}</td>

            <td>{{ $report->member->user_id ?? '-' }}</td>

            <td>{{ $report->member->fullname ?? '-' }}</td>

           <td class="center">
    {{ \Carbon\Carbon::parse($report->week_start)->format('d-m-Y') }}
    to
    {{ \Carbon\Carbon::parse($report->week_end)->format('d-m-Y') }}
</td>

      <td class="center">{{ $report->step_level }}</td>

<td class="right">
    {{ number_format($report->left_bv_before, 2) }}
</td>

<td class="right">
    {{ number_format($report->right_bv_before, 2) }}
</td>

<td class="right">
    {{ number_format($report->matching_bv, 2) }}
</td>

<td class="center">
    {{ $report->matched_pairs }}
</td>

<td class="right">
    ₹ {{ number_format(
        $report->matched_pairs > 0
            ? $report->gross_income / $report->matched_pairs
            : 0,
        2
    ) }}
</td>
            <td class="right">
                ₹ {{ number_format($report->payable_income, 2) }}
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

            <th colspan="7" class="right">
                Grand Total ({{ $reports->count() }} Record{{ $reports->count() > 1 ? 's' : '' }})
            </th>

            <th class="right">
                {{ number_format($totalMatchingBV, 2) }}
            </th>

            <th></th>

            <th></th>

            <th class="right">
                ₹ {{ number_format($totalPayableIncome, 2) }}
            </th>

            <th></th>

        </tr>

    </tfoot>

</table>

@endsection