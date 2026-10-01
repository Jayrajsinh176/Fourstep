<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FamilySaverBonusController extends Controller
{
    // Dynamic monthly family-saver bonus distribution to verified nominee claims.

    public function index(Request $request)
    {
        $user_id = $request->user_id;
        $month = $request->month;

        $query = DB::table('family_saver_bonuses')
            ->leftJoin('members','members.id','=','family_saver_bonuses.deceased_member_id')
            ->select(
                'family_saver_bonuses.*',
                'members.fullname as deceased_member_name'
            );

        if ($month) {
            $query->where('family_saver_bonuses.month_key', $month);
        }

        if ($user_id) {
            $member = Member::where('user_id', $user_id)->first();

            if ($member) {
                $query->where('family_saver_bonuses.nominee_member_id', $member->id);
            }
        }

        $rows = $query->orderBy('family_saver_bonuses.id','desc')->limit(100)->get();

        $data = [];
        $sr = 1;

        foreach ($rows as $row) {

            $data[] = [
                "sr_no" => $sr++,
                "transaction_id" => "FSB".str_pad($row->id,6,"0",STR_PAD_LEFT),
                "date" => $row->calculated_at
                    ? Carbon::parse($row->calculated_at)->format('Y-m-d')
                    : "-",
                "family_id" => $row->nominee_member_id,
                "deceased_member" => $row->deceased_member_name ?? "-",
               "combined_business" => $row->monthly_company_bv,
                "qualification_status" => $row->qualification_status,
                "earned" => $row->bonus_amount,
                "status" => $row->status,
                "wallet" => "On Hold"
            ];
        }

        return response()->json([
            "message" => "Family Saver bonus history",
            "data" => $data
        ]);
    }



 public function calculateMonthly(Request $request)
{
    $request->validate([
        'month' => 'required|date_format:Y-m',
    ]);

    $month = $request->month;

    // Family Saver requirement from plan
   $requiredMonthlyBv = 100;

    // Family Saver pool = 1% of company BV for the selected month
    $percentage = 1;

// Automatically calculate Company BV from approved purchases
$monthDate = Carbon::createFromFormat('Y-m', $month);

$companyBv = DB::table('purchases')
    ->where('status', 'approved')
    ->whereYear('purchase_date', $monthDate->year)
    ->whereMonth('purchase_date', $monthDate->month)
    ->sum('total_bv');

$companyBv = round((float) $companyBv, 2);

if ($companyBv <= 0) {
    return response()->json([
        'message' => 'Company BV not found for this month',
        'data' => [
            'month' => $month,
            'company_bv' => 0,
        ],
    ], 404);
}

// Save/update the automatically calculated Company BV
$existingCompanyBv = DB::table('company_bv_records')
    ->where('month_key', $month)
    ->first();

if ($existingCompanyBv) {
    DB::table('company_bv_records')
        ->where('month_key', $month)
        ->update([
            'total_bv' => $companyBv,
            'updated_at' => now(),
        ]);
} else {
    DB::table('company_bv_records')
        ->insert([
            'month_key' => $month,
            'total_bv' => $companyBv,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
}

$totalBonus = ($companyBv * $percentage) / 100;

    /*
     * Only verified death claims whose death date
     * falls in the selected month.
     */
$claims = DB::table('death_claims')
    ->join(
        'members',
        'members.id',
        '=',
        'death_claims.deceased_member_id'
    )
    ->where('death_claims.verification_status', 'verified')
    ->whereNotNull('death_claims.death_date')

    // Deceased member must be an active member
    ->where('members.status', 1)
    ->where('members.is_active', 1)
    ->where('members.account_status', 'active')

    ->whereRaw(
        "DATE_FORMAT(death_claims.death_date, '%Y-%m') = ?",
        [$month]
    )
    ->select('death_claims.*')
    ->get();

    if ($claims->isEmpty()) {
        return response()->json([
            'message' => 'No verified death claims for this month',
            'data' => [
                'month' => $month,
                'company_bv' => $companyBv,
                'total_bonus_pool' => $totalBonus,
                'qualified_claims' => 0,
                'inserted_rows' => 0,
            ],
        ]);
    }

/*
 * Find only claims where the deceased member
 * maintained at least 100 BV in each of the
 * three consecutive months before death.
 */
    $qualifiedClaims = collect();

    foreach ($claims as $claim) {

        $deathDate = Carbon::parse($claim->death_date);

        $qualified = true;

        for ($i = 1; $i <= 3; $i++) {

            $checkMonth = $deathDate->copy()->subMonths($i);

            $monthlyBv = DB::table('purchases')
                ->where('member_id', $claim->deceased_member_id)
                ->where('status', 'approved')
                ->whereYear('purchase_date', $checkMonth->year)
                ->whereMonth('purchase_date', $checkMonth->month)
                ->sum('total_bv');

            if ((float) $monthlyBv < $requiredMonthlyBv) {
                $qualified = false;
                break;
            }
        }

        if ($qualified) {
            $qualifiedClaims->push($claim);
        }
    }

    $qualifiedCount = $qualifiedClaims->count();

    if ($qualifiedCount === 0) {
        return response()->json([
            'message' => 'No qualifying Family Saver claims for this month',
            'data' => [
                'month' => $month,
                'company_bv' => $companyBv,
                'total_bonus_pool' => $totalBonus,
                'required_monthly_bv' => $requiredMonthlyBv,
                'qualified_claims' => 0,
                'inserted_rows' => 0,
            ],
        ]);
    }

 /*
 * Divide the 1% company BV pool
 * equally among all qualifying claims.
 */

    $bonusPerNominee = $totalBonus / $qualifiedCount;

    $inserted = 0;
    $skipped = 0;

    foreach ($qualifiedClaims as $claim) {

        /*
         * Prevent duplicate calculation for the
         * same death claim and month.
         */
        $exists = DB::table('family_saver_bonuses')
            ->where('death_claim_id', $claim->id)
            ->where('month_key', $month)
            ->exists();

        if ($exists) {
            $skipped++;
            continue;
        }

        $familySaverBonusId = DB::table('family_saver_bonuses')->insertGetId([
    'nominee_member_id' => $claim->nominee_member_id,
    'deceased_member_id' => $claim->deceased_member_id,
    'death_claim_id' => $claim->id,
    'month_key' => $month,
    'monthly_company_bv' => $companyBv,
    'bonus_percentage' => $percentage,
    'bonus_amount' => $bonusPerNominee,
    'qualification_status' => 'Qualified',
    'status' => 'pending',
    'calculated_at' => now(),
    'created_at' => now(),
    'updated_at' => now(),
]);

/*
 * Add Family Saver income to income_reports.
 */
DB::table('income_reports')->updateOrInsert(
    [
        'member_id' => $claim->nominee_member_id,
        'transaction_id' => 'FSB' . str_pad(
            (string) $familySaverBonusId,
            6,
            '0',
            STR_PAD_LEFT
        ),
    ],
    [
        'bonus_name' => 'Family Saver Bonus',
        'bonus_month' => $month,
        'earned_bonus' => round($bonusPerNominee, 2),
        'status' => 'pending',
        'entry_date' => now()->toDateString(),
        'activation_date' => null,
        'source_type' => 'family_saver',
        'updated_at' => now(),
        'created_at' => now(),
    ]
);

/*
 * Transfer deceased member's membership/package
 * to the nominee.
 *
 * IMPORTANT:
 * Do not change sponsor_id, parent_id, position,
 * PV, BV, wallet balance or genealogy.
 */

$deceasedMember = DB::table('members')
    ->where('id', $claim->deceased_member_id)
    ->first();

$nomineeMember = DB::table('members')
    ->where('id', $claim->nominee_member_id)
    ->first();

if ($deceasedMember && $nomineeMember) {

    $deceasedStep = (int) ($deceasedMember->package_step ?? 0);
    $nomineeStep = (int) ($nomineeMember->package_step ?? 0);

    $transferStep = max($deceasedStep, $nomineeStep);

    DB::table('members')
        ->where('id', $nomineeMember->id)
        ->update([
            'package_step' => $transferStep,
            'status' => 1,
            'is_active' => 1,
            'is_activated' => 1,
            'account_status' => 'active',
            'activation_date' => $nomineeMember->activation_date ?: now(),
            'updated_at' => now(),
        ]);
}

$inserted++;
    }

    return response()->json([
        'message' => 'Family Saver bonus calculated successfully.',
        'data' => [
            'month' => $month,
          'company_bv' => $companyBv,
            'bonus_percentage' => $percentage,
            'total_bonus_pool' => round($totalBonus, 2),
            'required_monthly_bv' => $requiredMonthlyBv,
            'verified_claims' => $claims->count(),
            'qualified_claims' => $qualifiedCount,
            'bonus_per_nominee' => round($bonusPerNominee, 2),
            'inserted_rows' => $inserted,
            'skipped_duplicates' => $skipped,
        ],
    ]);
}

}