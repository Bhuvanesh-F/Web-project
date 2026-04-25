<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** AppointmentController stub — M3 Shirish */
class AppointmentController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M3 Shirish'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M3 Shirish']);
    }

    public function destroy(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M3 Shirish']);
    }
}
