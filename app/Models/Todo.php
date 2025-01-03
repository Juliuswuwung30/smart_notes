<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    /** @use HasFactory<\Database\Factories\TodoFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'text',
        'is_finished',
        'note_id'
    ];

    public function fromnote(){
        return $this->belongsTo(Note::class);
    }
}
