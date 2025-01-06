<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Note;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Note::factory(10)->create([
            'user_id' => \Illuminate\Support\Str::uuid()
        ])->each(function ($note) {
            $note->todolist()->saveMany(
                \App\Models\Todo::factory(rand(3, 7))->make()
            );
        });
    }
}
