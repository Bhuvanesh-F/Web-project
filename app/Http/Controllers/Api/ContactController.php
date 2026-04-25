<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ContactController
 * POST /api/contact — public, throttle:10,1
 */
class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:100'],
            'email'   => ['required', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        DB::table('contact_messages')->insert([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'subject'    => $validated['subject'] ?? null,
            'message'    => $validated['message'],
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been received. We will get back to you within 24 hours.',
        ], 201);
    }
}
