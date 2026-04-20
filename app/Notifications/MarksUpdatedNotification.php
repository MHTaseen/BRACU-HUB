<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\AssignmentSubmission;

class MarksUpdatedNotification extends Notification
{
    public $submission;

    /**
     * Create a new notification instance.
     */
    public function __construct(AssignmentSubmission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $assignment = $this->submission->assignment;
        $type = $assignment->type ?? 'Task';
        
        return [
            'assignment_id' => $assignment->id,
            'title' => 'Marks Updated: ' . $assignment->title,
            'message' => "Your teacher has graded your {$type}. You scored {$this->submission->marks} / {$assignment->weight}.",
        ];
    }
}
