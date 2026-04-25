<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/** PetOwnerController stub — M4 Amirah */
class PetOwnerController extends Controller
{
    public function pets(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M4 Amirah']);
    }

    public function appointments(int $id): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M4 Amirah']);
    }
}
