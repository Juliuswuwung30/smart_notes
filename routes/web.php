<?php

use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/notes', [NoteController::class, 'getUserNotes']);

Route::patch('/notes/{note_id}', [NoteController::class, 'editNote'])->withoutMiddleware(VerifyCsrfToken::class);

Route::delete('/notes/{note_id}', [NoteController::class, 'deleteNote'])->withoutMiddleware(VerifyCsrfToken::class);

Route::get('/notes/{note_id}/todos', [TodoController::class, 'viewTodos']);

Route::post('/notes/{note_id}/todos', [TodoController::class, 'createTodo'])->withoutMiddleware(VerifyCsrfToken::class);;

Route::put('/notes/{note_id}/todos/{todo_id}', [TodoController::class, 'updateTodo'])->withoutMiddleware(VerifyCsrfToken::class);

Route::delete('/notes/{note_id}/todos/{todo_id}', [TodoController::class, 'deleteTodo'])->withoutMiddleware(VerifyCsrfToken::class);
