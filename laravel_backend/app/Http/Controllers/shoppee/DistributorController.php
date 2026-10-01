<?php
namespace App\Http\Controllers\Shoppee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shoppee_Distributor;
class DistributorController extends Controller
{
    // Get distributor by distributor_id
    public function show($id)
{
    $distributor = Shoppee_Distributor::whereRaw(
        'TRIM(distributor_id) = ?',
        [trim($id)]
    )->first();

    if (!$distributor) {
        return response()->json([
            'success' => false,
            'message' => 'Distributor not found'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => [
            'distributor_id' => $distributor->distributor_id,
            'name' => $distributor->name,
            'mobile' => $distributor->mobile
        ]
    ]);
}
}