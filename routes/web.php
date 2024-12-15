<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/notes', [NoteController::class, 'getUserNotes']);

Route::put('/notes/{note_id}', [NoteController::class, 'editNote'])->withoutMiddleware(VerifyCsrfToken::class);

