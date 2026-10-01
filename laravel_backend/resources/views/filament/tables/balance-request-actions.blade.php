@if ($record->status === 'pending')
    <a href="{{ url('/admin/balance-request/approve/' . $record->id) }}"
       style="padding:5px 12px; background:#16a34a; color:#fff; border-radius:6px; margin-right:6px; text-decoration:none; font-size:12px;">
        Approve
    </a>

    <a href="{{ url('/admin/balance-request/reject/' . $record->id) }}"
       style="padding:5px 12px; background:#dc2626; color:#fff; border-radius:6px; text-decoration:none; font-size:12px;">
        Reject
    </a>
@else
    -
@endif