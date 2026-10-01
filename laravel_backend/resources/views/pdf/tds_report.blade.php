@extends('pdf.layouts.report')

@section('title')
TDS REPORT
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">
                {{ $reports->count() }}
            </div>
        </td>

        <td>
            <div class="summary-title">Total Gross Amount</div>
            <div class="summary-value">
                ₹ {{ number_format($totalGross,2) }}
            </div>
        </td>

        <td>
            <div class="summary-title">Total TDS</div>
            <div class="summary-value">
                ₹ {{ number_format($totalTds,2) }}
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

            <th>Payment Date</th>

            <th>Member ID</th>

            <th>Name</th>

            <th>Mobile</th>

            <th>PAN No.</th>

            <th>Gross Amount</th>

            <th>TDS</th>

        </tr>

    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">
                {{ $loop->iteration }}
            </td>

            <td class="center">
                {{ \Carbon\Carbon::parse($report->paid_at)->format('d-m-Y') }}
            </td>

            <td>
                {{ $report->member->user_id ?? '-' }}
            </td>

            <td>
                {{ $report->member->fullname ?? '-' }}
            </td>

            <td>
                {{ $report->member->mobile_no ?? '-' }}
            </td>

            <td>
                {{ $report->kyc->pan_number ?? '-' }}
            </td>

            <td class="right">
                ₹ {{ number_format($report->gross_amount,2) }}
            </td>

            <td class="right">
                ₹ {{ number_format($report->tds,2) }}
            </td>

        </tr>

    @empty

        <tr>

            <td colspan="8" class="center">
                No records found.
            </td>

        </tr>

    @endforelse

    </tbody>

    <tfoot>

        <tr>

            <th colspan="6" class="right">
                Grand Total ({{ $reports->count() }} Records)
            </th>

            <th class="right">
                ₹ {{ number_format($totalGross,2) }}
            </th>

            <th class="right">
                ₹ {{ number_format($totalTds,2) }}
            </th>

        </tr>

    </tfoot>

</table>

@endsection