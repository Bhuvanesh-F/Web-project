<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** PetController stub — M4 Amirah */
class PetController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M4 Amirah']);
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M4 Amirah'], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return response()->json(['success' => true, 'message' => 'Implemented by M4 Amirah']);
    }
}
