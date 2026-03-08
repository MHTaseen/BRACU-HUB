<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['section_id', 'title', 'description', 'weight', 'due_date'];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
