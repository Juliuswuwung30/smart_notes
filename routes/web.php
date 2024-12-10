<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/notes', [NoteController::class, 'getUserNotes']);