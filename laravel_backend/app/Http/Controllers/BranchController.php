<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Member;
use Carbon\Carbon;

class BranchController extends Controller
{

   
    public function index(Request $request)
    {
        $limit = $request->limit ?? 200;
        $user_id = $request->user_id;

        $query = DB::table('branches')->orderBy('id', 'desc');

        if ($user_id) {
            $query->where('user_id', $user_id);
        }

        $branches = $query->limit($limit)->get();

        $data = [];

        foreach ($branches as $row) {

            $data[] = [
                "id" => $row->id,
                "user_id" => $row->user_id ?? '--',
                "shopee_name" => $row->shopee_name ?? '--',
                "shopee_type" => $row->shopee_type ?? '--',
                "date" => $this->formatDate($row->created_at ?? null),
                "contact_person" => $row->contact_person ?? '--',
                "state" => $row->state ?? '--',
                "district" => $row->district ?? '--',
                "city_taluka" => $row->city_taluka ?? '--',
                "status" => $this->formatStatus($row->status ?? null)
            ];
        }

        return response()->json([
            "message" => "Branch list fetched successfully",
            "data" => $data
        ]);
    }


    
    public function referralBranch(Request $request)
    {
        $memberUserId = $request->header('X-Auth-Member') ?: $request->query('user_id');

        if (!$memberUserId) {
            return response()->json([
                "message" => "User ID missing"
            ], 400);
        }

        $member = Member::where('user_id', $memberUserId)->first();

        if (!$member) {
            return response()->json([
                "message" => "Member not found"
            ], 404);
        }

        $referrals = Member::where('sponsor_id', $member->id)
            ->select(['id', 'user_id'])
            ->get();

        $userIds = collect([$member->user_id, (string) $member->id, $member->id])
            ->merge($referrals->pluck('user_id'))
            ->merge($referrals->pluck('id'))
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->unique()
            ->values();

        $branchesQuery = DB::table('branches')->orderBy('id', 'desc');

        if (Schema::hasColumn('branches', 'user_id')) {
            $branchesQuery->whereIn('user_id', $userIds->all());
        } elseif (Schema::hasColumn('branches', 'member_id')) {
            $branchesQuery->whereIn('member_id', $userIds->all());
        } else {
            return response()->json([
                "message" => "Branch mapping columns not found"
            ], 500);
        }

        $branches = $branchesQuery->limit(200)->get();

        $data = [];

        foreach ($branches as $row) {

            $data[] = [
                "id" => $row->id,
                "user_id" => $row->user_id ?? '--',
                "shopee_name" => $row->shopee_name ?? '--',
                "branch_type" => $row->branch_type ?? $row->shopee_type ?? '--',
                "shopee_type" => $row->shopee_type ?? $row->branch_type ?? '--',
                "date" => $this->formatDate($row->created_at ?? null),
                "contact_person" => $row->contact_person ?? '--',
                "state" => $row->state ?? '--',
                "district" => $row->district ?? '--',
                "city_taluka" => $row->city_taluka ?? '--',
                "status" => $this->formatStatus($row->status ?? null)
            ];
        }

        return response()->json([
            "message" => "Referral branch fetched",
            "member" => [
                "id" => $member->id,
                "user_id" => $member->user_id,
                "fullname" => $member->fullname
            ],
            "data" => $data
        ]);
    }


    private function formatDate($date)
    {
        if (!$date) return "--";

        return Carbon::parse($date)->format('d-m-Y');
    }

    private function formatStatus($status)
    {
        if ($status == 1) {
            return "Active";
        }

        if ($status == 0) {
            return "Inactive";
        }

        return "--";
    }
}