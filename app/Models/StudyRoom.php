<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyRoom extends Model
{
    protected $fillable = [
        'name',
        'description',
        'course_id',
        'section_id',
        'created_by',
        'is_active',
        'archived_at',
        'whiteboard_data',
        'notes_data',
        'chat_messages',
    ];

    protected $casts = [
        // whiteboard_data and notes_data are stored as plain text (base64 / raw string)
        // Do NOT cast them to array — they are not JSON arrays
        'archived_at'   => 'datetime',
        'chat_messages' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(StudyRoomParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(StudyRoomParticipant::class)->whereNull('left_at');
    }
}
