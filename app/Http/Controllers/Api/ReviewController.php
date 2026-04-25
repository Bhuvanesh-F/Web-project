<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ReviewController
 * GET /api/reviews  — public, no auth
 * POST /api/reviews — requires auth:sanctum + patient/pet_owner role
 */
class ReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = DB::table('reviews')
            ->join('users', 'reviews.user_id', '=', 'users.id')
            ->where('reviews.is_approved', true)
            ->orderByDesc('reviews.created_at')
            ->select(
                'reviews.id',
                'reviews.rating',
                'reviews.title',
                'reviews.body',
                'reviews.created_at',
                'users.name as reviewer_name',
                'users.role as reviewer_role'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $reviews,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title'  => ['nullable', 'string', 'max:150'],
            'body'   => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $id = DB::table('reviews')->insertGetId([
            'user_id'     => $request->user()->id,
            'rating'      => $validated['rating'],
            'title'       => $validated['title'] ?? null,
            'body'        => $validated['body'],
            'is_approved' => false,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for your review. It will appear after moderation.',
            'data'    => ['id' => $id],
        ], 201);
    }
}
