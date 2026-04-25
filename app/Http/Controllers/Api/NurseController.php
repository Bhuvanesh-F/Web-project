<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** NurseController stub — M3 Shirish */
class NurseController extends Controller
{
    public function checklist(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }

    public function addChecklist(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M3 Shirish'], 201);
    }

    public function updateChecklist(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M3 Shirish']);
    }

    public function patients(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }
}
