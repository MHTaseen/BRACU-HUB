<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = ['type', 'section_id', 'title', 'description', 'weight', 'max_marks', 'due_date'];

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

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
