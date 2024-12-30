<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    /** @use HasFactory<\Database\Factories\TodoFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'is_finished',
        'note_id'
    ];

    public function note()
{
    return $this->belongsTo(Note::class, 'note_id', 'id');
}
}
