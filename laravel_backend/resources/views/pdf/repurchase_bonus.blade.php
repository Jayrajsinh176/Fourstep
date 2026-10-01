@extends('pdf.layouts.report')

@section('title')
Repurchase Bonus Report
@endsection

@section('summary')

<table class="summary">

    <tr>

        <td>
            <div class="summary-title">Total Records</div>
            <div class="summary-value">{{ $reports->count() }}</div>
        </td>

        <td>
          <div class="summary-title">Total Purchase BV</div>
            <div class="summary-value">
    {{ number_format($totalPurchase,2) }}
</div
        </td>

        <td>
            <div class="summary-title">Total Bonus</div>
            <div class="summary-value">
                ₹ {{ number_format($totalBonus,2) }}
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
           <th>Bonus Week</th>
           <th>Purchase BV</th>
            <th>Bonus Amount</th>
            <th>Status</th>

        </tr>

    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">{{ $loop->iteration }}</td>

            <td>{{ $report->member->user_id }}</td>

            <td>{{ $report->member->fullname }}</td>

<td class="center">
    {{ \Carbon\Carbon::parse($report->week_start)->format('d M Y') }}
    -
    {{ \Carbon\Carbon::parse($report->week_end)->format('d M Y') }}
</td>

         <td class="right">
    {{ number_format($report->purchase_amount, 2) }}
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
                ₹ {{ number_format($totalPurchase,2) }}
            </th>

            <th class="right">
                ₹ {{ number_format($totalBonus,2) }}
            </th>

            <th></th>

        </tr>

    </tfoot>

</table>

@endsection