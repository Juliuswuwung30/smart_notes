<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Note extends Model
{
    use HasFactory;

    // Indicates that the primary key is not auto-incrementing
    public $incrementing = false;

    // Specifies the primary key's type
    protected $keyType = 'string';

    // Allows mass assignment for all attributes
    protected $guarded = [];

    // Boot method to add UUID generation for the primary key
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // Relationship with the Todo model
    public function todolist()
    {
        return $this->hasMany(Todo::class);
        // public function todolist()
        // {
        //     return $this->hasMany(Todo::class, 'note_id', 'id');
        // }
    }
}
