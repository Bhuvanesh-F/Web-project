<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** ReceptionistController stub — M3 Shirish */
class ReceptionistController extends Controller
{
    public function appointments(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => [], 'message' => 'Implemented by M3 Shirish']);
    }
}
