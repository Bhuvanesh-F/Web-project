<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * DoctorController — stub
 * Full implementation owned by Shirish (M3, feature/human-api).
 * These stubs ensure all registered API routes resolve without errors
 * during integration and Ayman's demo.
 */
class DoctorController extends Controller
{
    public function appointments(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }

    public function patients(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }

    public function schedule(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }

    public function updateProfile(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M3 Shirish']);
    }
}
