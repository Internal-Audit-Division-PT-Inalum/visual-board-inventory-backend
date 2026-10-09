<?php

namespace App\Http\Controllers\Api\Auth;

use App\Domains\HR\Models\Employee;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePinRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\UserSessionResource;
use App\Services\Inventory\InventoryAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private InventoryAuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->validated('namecode'),
            $request->validated('pin')
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $result['token'],
                'must_change_pin' => $result['must_change_pin'],
                'employee' => new UserSessionResource($result['employee']),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        // Find employee associated with the authenticated user
        $employee = Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'must_change_pin' => $employee->must_change_pin,
                'employee' => new UserSessionResource($employee),
            ],
        ]);
    }

    public function changePin(ChangePinRequest $request): JsonResponse
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (! $employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found.',
            ], 404);
        }

        $this->authService->changePin(
            $employee,
            $request->validated('current_pin'),
            $request->validated('new_pin')
        );

        return response()->json([
            'success' => true,
            'message' => 'PIN berhasil diubah.',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }
}
