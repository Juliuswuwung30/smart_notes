<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Todo;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{
    public function createTodo(Request $request, $note_id){
        //         Endpoint: POST /notes/{note_id}/todos

        // Request Body:

        // {
        //   "title": "Buy groceries",
        //   "text": "Pick up items from the list",
        //   "is_finished": false
        // }
        // Response:

        // {
        //   "id": "1",
        //   "note_id": "1",
        //   "title": "Buy groceries",
        //   "text": "Pick up items from the list",
        //   "is_finished": false
        // }
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'is_finished' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid title, text, or is_finished parameter',
                    'details' => $validator->errors(),
                ]
            ], 400);
        }

        $todo = Todo::create([
            'title' => $request->input('title'),
            'is_finished' => $request->input('is_finished'),
            'note_id' => $note_id,
        ]);

        return response()->json([
            'id' => $todo->id,
            'note_id' => $note_id,
            'title' => $todo->title,
            'is_finished' => $todo->is_finished,
        ], 200);
    }

    public function updateTodo(Request $request, $note_id, $todo_id){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'is_finished' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid title, text, or is_finished parameter',
                    'details' => $validator->errors(),
                ]
            ], 400);
        }

        $todo = Todo::find($todo_id);

        if (!$todo) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'Todo not found for the specified user'
                ]
            ], 404);
        }

        $todo->title = $request->input('title');
        $todo->is_finished = $request->input('is_finished');
        $todo->save();

        return response()->json([
            'id' => $todo->id,
            'note_id' => $note_id,
            'title' => $todo->title,
            'is_finished' => $todo->is_finished,
        ], 200);
    }

    public function deleteTodo($note_id, $todo_id){
        $todo = Todo::find($todo_id);

        if (!$todo) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'Todo not found for the specified user'
                ]
            ], 404);
        }

        $todo->delete();

        return response()->json([
            'message' => 'Todo deleted successfully'
        ], 200);
    }


}

