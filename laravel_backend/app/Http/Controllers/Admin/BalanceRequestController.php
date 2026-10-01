<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseBalanceRequest;

class BalanceRequestController extends Controller
{
    public function approve($id)
    {
        $record = PurchaseBalanceRequest::findOrFail($id);

        if ($record->status !== 'pending') {
            return redirect()->back()->with('error', 'This request is already processed.');
        }

        $record->update([
            'status' => 'approved',
        ]);

        return redirect()->back()->with('success', 'Balance request approved successfully.');
    }

    public function reject($id)
    {
        $record = PurchaseBalanceRequest::findOrFail($id);

        if ($record->status !== 'pending') {
            return redirect()->back()->with('error', 'This request is already processed.');
        }

        $record->update([
            'status' => 'rejected',
        ]);

        return redirect()->back()->with('success', 'Balance request rejected successfully.');
    }
}