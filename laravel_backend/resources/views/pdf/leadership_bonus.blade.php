@extends('pdf.layouts.report')

@section('title')
Leadership Gift Achievers Report
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
            <div class="summary-title">Pending</div>
            <div class="summary-value">
                {{ $reports->where('status', 'pending')->count() }}
            </div>
        </td>

        <td>
            <div class="summary-title">Approved</div>
            <div class="summary-value">
                {{ $reports->where('status', 'approved')->count() }}
            </div>
        </td>

        <td>
            <div class="summary-title">Delivered</div>
            <div class="summary-value">
                {{ $reports->where('status', 'delivered')->count() }}
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
            <th>Rank</th>
            <th>Gift</th>
            <th>Qualified Date</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>

    @forelse($reports as $report)

        <tr>

            <td class="center">
                {{ $loop->iteration }}
            </td>

            <td>
                {{ $report->member->user_id ?? '-' }}
            </td>

            <td>
                {{ $report->member->fullname ?? '-' }}
            </td>

            <td class="center">
                {{ $report->rank_name ?? '-' }}
            </td>

            <td>
                {{ $report->gift_name ?? '-' }}
            </td>

            <td class="center">
                {{ $report->qualified_date
                    ? \Carbon\Carbon::parse($report->qualified_date)->format('d M Y')
                    : '-' }}
            </td>

            <td class="center">

                @if($report->status === 'delivered')

                    <span style="color:#16A34A;font-weight:bold;">
                        Delivered
                    </span>

                @elseif($report->status === 'approved')

                    <span style="color:#2563EB;font-weight:bold;">
                        Approved
                    </span>

                @elseif($report->status === 'pending')

                    <span style="color:#D97706;font-weight:bold;">
                        Pending
                    </span>

                @else

                    {{ ucfirst($report->status ?? '-') }}

                @endif

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

            <th colspan="6" class="right">
                Total Records
                ({{ $reports->count() }}
                Record{{ $reports->count() > 1 ? 's' : '' }})
            </th>

            <th class="center">
                {{ $reports->count() }}
            </th>

        </tr>

    </tfoot>

</table>

@endsection