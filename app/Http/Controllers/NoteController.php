<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OpenAIService;
use App\Models\Todo;
use App\Models\Note;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;


class NoteController extends Controller
{
    //

    /**
     * Get User Note
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserNote(Request $request, $note_id)
    {

        if (!$note_id || !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $note_id)) {
            return response()->json([
            'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid note id parameter'
                ]
            ], 400);
        }

        $userId = $request->query('user_id');
        $note = Note::with('todolist')->find($note_id);

        if (!$note) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'Note not found for the specified user'
                ]
            ], 404);
        }

        $response = [
            'id' => $note->id,
            'title' => $note->title,
            'content' => $note->content,
            'updatedAt' => $note->updated_at->toIso8601String(),
            'icon' => 'Chart.small',
            'isComplete' => $note->todolist()->where('is_finished', false)->doesntExist(),
            'todoList' => $note->todolist->map(function ($todo) {
                return [
                    'id' => $todo->id,
                    'todo' => $todo->text,
                    'isCompleted' => (bool) $todo->is_finished
                ];
            })
        ];

        return response()->json($response, 200);
    }

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
        $notes = Note::with('todolist')->where('user_id', $userId)->get();

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
                'icon' => 'Chart.small',
                'isComplete' => $note->todolist()->where('is_finished', false)->doesntExist(),
                'todoList' => $note->todolist->map(function ($todo) {
                    return [
                        'id' => $todo->id,
                        'todo' => $todo->text,
                        'isCompleted' => (bool) $todo->is_finished
                    ];
                })
            ];
        });

        return response()->json($response, 200);
    }


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
        $notes = Note::with('todolist')->where('user_id', $userId)
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
                'updatedAt' => $note->updated_at->toIso8601String(),
                'icon' => 'Chart.small',
                'isComplete' => $note->todolist()->where('is_finished', false)->doesntExist(),
                'todoList' => $note->todolist->map(function ($todo) {
                    return [
                        'id' => $todo->id,
                        'todo' => $todo->text,
                        'isCompleted' => (bool) $todo->is_finished
                    ];
                })
            ];
        });

        return response()->json($response, 200);
    }

    public function createNote(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'title' => 'string',
            'content' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid parameters'
                ]
            ], 400);
        }

        $note = Note::create([
            'user_id' => $request->input('user_id'),
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'icon' => $request->input('icon'),
        ]);

        return response()->json(['id' => $note->id], 201);
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


    public function generateTodos(Request $request, string $noteId, OpenAIService $openAI): JsonResponse
    {
        $note = Note::find($noteId);
        
        if (!$note) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'No notes found for the specified note'
                ]
            ], 404);
        }
        
        // Generate todos using OpenAI
        $generatedTodos = $openAI->generateTodos($note->content);
        
        if (empty($generatedTodos)) {
            return response()->json([]);
        }
        
        // Create todos in database
        $todos = collect($generatedTodos)->map(function ($todoText) use ($noteId) {
            return Todo::create([
                'text' => $todoText,
                'is_finished' => false,
                'note_id' => $noteId
            ]);
        })->map(function ($todo) {
            return [
                'id' => $todo->id,
                'todo' => $todo->text,
                'isCompleted' => (bool) $todo->is_finished
            ];
        });
        
        return response()->json($todos);
    }

}
