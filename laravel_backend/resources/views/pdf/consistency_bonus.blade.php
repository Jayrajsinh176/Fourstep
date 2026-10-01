@extends('pdf.layouts.report')

@section('title')
Consistency Bonus Report
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">{{ $reports->count() }}</div>
        </td>

        <td>
            <div class="summary-title">Total Credit</div>
            <div class="summary-value">
                ₹ {{ number_format($totalCredit, 2) }}
            </div>
        </td>

        <td>
            <div class="summary-title">Total Debit</div>
            <div class="summary-value">
                ₹ {{ number_format($totalDebit, 2) }}
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
            <th>Description</th>
            <th>Credit</th>
            <th>Debit</th>
            <th>Created Date</th>
        </tr>

    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">{{ $loop->iteration }}</td>

            <td>{{ $report->member->user_id ?? '-' }}</td>

            <td>{{ $report->member->fullname ?? '-' }}</td>

            <td>{{ $report->detail }}</td>

            <td class="right">
                ₹ {{ number_format($report->credit, 2) }}
            </td>

            <td class="right">
                ₹ {{ number_format($report->debit, 2) }}
            </td>

            <td class="center">
                {{ \Carbon\Carbon::parse($report->created_at)->format('d M Y h:i A') }}
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="7" class="center">
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
                ₹ {{ number_format($totalCredit, 2) }}
            </th>

            <th class="right">
                ₹ {{ number_format($totalDebit, 2) }}
            </th>

            <th></th>

        </tr>

    </tfoot>

</table>

@endsection