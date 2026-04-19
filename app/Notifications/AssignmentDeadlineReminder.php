<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

use App\Models\Assignment;

class AssignmentDeadlineReminder extends Notification
{

    public $assignment;
    public $daysLeft;

    /**
     * Create a new notification instance.
     */
    public function __construct(Assignment $assignment, $daysLeft)
    {
        $this->assignment = $assignment;
        $this->daysLeft = $daysLeft;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $type = $this->assignment->type ?? 'Assignment';
        return (new MailMessage)
            ->subject('Deadline Reminder: ' . $this->assignment->title)
            ->line('URGENT: This is a reminder that your ' . strtolower($type) . ' "' . $this->assignment->title . '" is due in ' . $this->daysLeft . ' day(s)!')
            ->action('View Dashboard', url('/dashboard'))
            ->line('Please make sure to submit it on time!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $type = $this->assignment->type ?? 'Assignment';
        return [
            'assignment_id' => $this->assignment->id,
            'title' => 'Deadline Reminder: ' . $this->assignment->title,
            'message' => 'Your ' . strtolower($type) . ' is due in ' . $this->daysLeft . ' day(s)!',
        ];
    }
}
