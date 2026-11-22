<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

class NoteRecord extends Model
{
    protected $table = 'notes';

    protected $fillable = ['parent_id', 'title', 'content', 'tags', 'depth', 'archived'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'depth' => 'integer',
            'archived' => 'boolean',
        ];
    }
}
