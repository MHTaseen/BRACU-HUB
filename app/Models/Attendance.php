<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['section_id', 'class_date'];

    protected function casts(): array
    {
        return [
            'class_date' => 'date',
        ];
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function records()
    {
        return $this->hasMany(AttendanceRecord::class);
    }
}
