<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Assignment;
use App\Models\User;

class TeacherManualReminder extends Notification
{
    public $assignment;
    public $teacher;
    public $customMessage;

    /**
     * @param Assignment $assignment
     * @param User       $teacher        The faculty member sending the reminder
     * @param string     $customMessage  Optional personal message from the teacher
     */
    public function __construct(Assignment $assignment, User $teacher, string $customMessage = '')
    {
        $this->assignment    = $assignment;
        $this->teacher       = $teacher;
        $this->customMessage = $customMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $type     = $this->assignment->type ?? 'Assignment';
        $due      = $this->assignment->due_date->format('M d, Y');
        $teacher  = $this->teacher->name;

        $mail = (new MailMessage)
            ->subject('📢 Reminder from ' . $teacher . ': ' . $this->assignment->title)
            ->line('Your instructor **' . $teacher . '** sent you a personal reminder about the following ' . strtolower($type) . ':')
            ->line('**' . $this->assignment->title . '** — Due: ' . $due);

        if (!empty($this->customMessage)) {
            $mail->line('---')
                 ->line('**Teacher\'s Note:** ' . $this->customMessage)
                 ->line('---');
        }

        return $mail
            ->action('View Dashboard', url('/dashboard'))
            ->line('Please make sure to submit on time!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $type    = $this->assignment->type ?? 'Assignment';
        $due     = $this->assignment->due_date->format('M d, Y');
        $teacher = $this->teacher->name;

        $message = '📢 ' . $teacher . ' reminded you about "' . $this->assignment->title . '" due ' . $due . '.';
        if (!empty($this->customMessage)) {
            $message .= ' Note: ' . $this->customMessage;
        }

        return [
            'assignment_id' => $this->assignment->id,
            'title'         => '📢 Manual Reminder: ' . $this->assignment->title,
            'message'       => $message,
        ];
    }
}
