<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Domains\Inventory\Models\AssetLoan;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\Inventory\AssetLoanResource;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function mine(Request $request)
    {
        $user = $request->user();

        if (! $user->employee) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki profil pegawai yang terhubung.',
            ], 403);
        }

        $query = AssetLoan::with('item')
            ->where('employee_id', $user->employee->id)
            ->latest('borrowed_at');

        if ($request->query('status') !== 'all') {
            $query->whereNull('fully_returned_at');
        }

        $loans = $query->paginate((int) $request->query('per_page', 100));

        return AssetLoanResource::collection($loans)->additional([
            'success' => true,
        ]);
    }
}
