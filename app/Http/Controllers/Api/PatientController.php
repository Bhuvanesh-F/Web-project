<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** PatientController stub — M3 Shirish */
class PatientController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }

    public function appointments(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }

    public function records(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }

    public function updateProfile(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M3 Shirish']);
    }
}
