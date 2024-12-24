<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Todo;
use Illuminate\Support\Facades\Validator;

class TodoController extends Controller
{

    public function viewTodos($note_id){

        $todos = Todo::where('note_id', $note_id)->get();

        if ($todos->isEmpty()) {
            return response()->json([
                'error' => [
                    'code' => '404_NOT_FOUND',
                    'message' => 'No todos found for the specified note'
                ]
            ], 404);
        }

        $response = $todos->map(function ($todo) {
            return [
                'id' => $todo->id,
                'note_id' => $todo->note_id,
                'text' => $todo->text,
                'is_finished' => $todo->is_finished
            ];
        });

        return response()->json($response, 200);
    }
    public function createTodo(Request $request, $note_id){
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'is_finished' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid text, text, or is_finished parameter',
                    'details' => $validator->errors(),
                ]
            ], 400);
        }

        $todo = Todo::create([
            'text' => $request->input('text'),
            'is_finished' => $request->input('is_finished'),
            'note_id' => $note_id,
        ]);

        return response()->json([
            'id' => $todo->id,
            'note_id' => $note_id,
            'text' => $todo->text,
            'is_finished' => $todo->is_finished,
        ], 200);
    }

    public function updateTodo(Request $request, $note_id, $todo_id){
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'is_finished' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => [
                    'code' => '400_BAD_REQUEST',
                    'message' => 'Missing or invalid text, text, or is_finished parameter',
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

        $todo->text = $request->input('text');
        $todo->is_finished = $request->input('is_finished');
        $todo->save();

        return response()->json([
            'id' => $todo->id,
            'note_id' => $note_id,
            'text' => $todo->text,
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

