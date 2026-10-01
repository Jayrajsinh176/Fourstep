@extends('pdf.layouts.report')

@section('title')
EXPENSE REPORT
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">
                {{ $expenses->count() }}
            </div>
        </td>

        <td>
            <div class="summary-title">Total Expense</div>
            <div class="summary-value">
                ₹ {{ number_format($totalExpense,2) }}
            </div>
        </td>

        <td>
            <div class="summary-title">Report Period</div>
            <div class="summary-value">
                {{ \Carbon\Carbon::parse($from)->format('d-m-Y') }}
                <br>
                to
                <br>
                {{ \Carbon\Carbon::parse($to)->format('d-m-Y') }}
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
            <th>Date</th>
            <th>Expense Type</th>
            <th>Payment Mode</th>
            <th>Remarks</th>
            <th>Amount</th>
        </tr>
    </thead>

    <tbody>

    @forelse($expenses as $expense)

        <tr>

            <td class="center">
                {{ $loop->iteration }}
            </td>

            <td class="center">
                {{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}
            </td>

            <td>
                {{ $expense->expense_type }}
            </td>

            <td class="center">
                {{ $expense->payment_mode }}
            </td>

            <td>
                {{ $expense->remarks ?? '-' }}
            </td>

            <td class="right">
                ₹ {{ number_format($expense->amount,2) }}
            </td>

        </tr>

    @empty

        <tr>
            <td colspan="6" class="center">
                No records found.
            </td>
        </tr>

    @endforelse

    </tbody>

    <tfoot>

        <tr>

            <th colspan="5" class="right">
                Grand Total ({{ $expenses->count() }} Record{{ $expenses->count() > 1 ? 's' : '' }})
            </th>

            <th class="right">
                ₹ {{ number_format($totalExpense,2) }}
            </th>

        </tr>

    </tfoot>

</table>

@endsection