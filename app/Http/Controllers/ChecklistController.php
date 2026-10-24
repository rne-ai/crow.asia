<?php

namespace App\Http\Controllers;

use App\Models\ChecklistProgress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_id' => 'required|string|max:32',
            'chapter_slug' => 'required|string|max:32',
            'item_index' => 'required|integer|min:0',
            'is_completed' => 'required|boolean',
        ]);

        /** @var User|null $user */
        $user = $request->user();

        if ($user) {
            ChecklistProgress::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'book_id' => $validated['book_id'],
                    'chapter_slug' => $validated['chapter_slug'],
                    'item_index' => $validated['item_index'],
                ],
                [
                    'is_completed' => $validated['is_completed'],
                    'updated_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'is_completed' => $validated['is_completed'],
                'authenticated' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'is_completed' => $validated['is_completed'],
            'authenticated' => false,
        ]);
    }
}
