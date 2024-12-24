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

    public function editNote(Request $request, $note_id){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid title or content parameter',
                    'details' => $validator->errors(),
                ]
            ], 400);
        }

        $note = Note::find($note_id);

        if (!$note) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'Note not found for the specified user'
                ]
            ], 404);
        }

        $note->title = $request->input('title');
        $note->content = $request->input('content');
        $note->save();

        return response()->json([
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'updatedAt' => $note->updated_at->toIso8601String(),
            'icon'=>"Chart.small"
        ], 200);

    }


    public function deleteNote(Request $request, $note_id){

        $note = Note::find($note_id);

        if (!$note) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'Note not found for the specified user'
                ]
            ], 404);
        }

        $note->delete();

        return response()->json([
            'message' => "Note deleted successfully"
        ], 200);

    }


}
