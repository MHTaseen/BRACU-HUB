<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    protected $fillable = [
        'section_id',
        'title',
        'description',
        'file_path',
        'type',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
