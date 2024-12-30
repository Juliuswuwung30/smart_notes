<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    //

    /**
     * Get User Notes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserNotes(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'user_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid user_id parameter'
                ]
            ], 400);
        }

        $userId = $request->query('user_id');
        $notes = Note::where('user_id', $userId)->get();

        if ($notes->isEmpty()) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'No notes found for the specified user'
                ]
            ], 404);
        }

        $response = $notes->map(function ($note) {
            return [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'updatedAt' => $note->updated_at->toIso8601String(),
                'isComplete' => $note->todolist()->where('is_finished', false)->doesntExist(),
            ];
        });

        return response()->json($response, 200);
    }

    /**
     * Get Completed User Notes
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompletedUserNotes(Request $request)
    {
        // Validate the request query
        $validator = Validator::make($request->query(), [
            'user_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid user_id parameter'
                ]
            ], 400);
        }

        // Fetch completed notes
        $userId = $request->query('user_id');

        // QUERY TO GET COMPLETED NOTES
        $notes = Note::where('user_id', $userId)
            ->whereDoesntHave('todolist', function ($query) {
                $query->where('is_finished', false);
            })
            ->get();

        // Check if the query returns empty data
        if ($notes->isEmpty()) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'No completed notes found for the specified user'
                ]
            ], 404);
        }

        // Map response for JSON
        $response = $notes->map(function ($note) {
            return [
                'id' => $note->id,
                'title' => $note->title,
                'content' => $note->content,
                'icon' => 'Chart.small', // Assuming a static icon for now
                'updatedAt' => $note->updated_at->toIso8601String(),
            ];
        });

        return response()->json($response, 200);
    }
}
