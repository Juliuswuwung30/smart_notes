<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/notes', [NoteController::class, 'getUserNotes']);
Route::post('/notes', [NoteController::class, 'createNote'])->withoutMiddleware(VerifyCsrfToken::class);