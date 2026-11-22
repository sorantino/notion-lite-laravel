<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Model;

class PageRecord extends Model
{
    protected $table = 'pages';

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
