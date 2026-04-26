<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentSubmission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'file_path',
        'marks_obtained',
        'total_marks',
    ];

    protected $casts = [
        'marks_obtained' => 'float',
        'total_marks'    => 'float',
    ];

    /**
     * Returns 0–100 performance percentage, or null if not graded.
     */
    public function performancePercent(): ?float
    {
        if ($this->marks_obtained === null || !$this->total_marks) {
            return null;
        }
        return round(($this->marks_obtained / $this->total_marks) * 100, 1);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
