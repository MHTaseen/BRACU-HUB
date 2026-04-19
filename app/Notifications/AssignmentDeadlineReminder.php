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
    public $timeLeft;
    public $unit; // 'days' or 'minutes'

    /**
     * Create a new notification instance.
     *
     * @param Assignment $assignment
     * @param int        $timeLeft  Number of days or minutes remaining
     * @param string     $unit      'days' (default) or 'minutes'
     */
    public function __construct(Assignment $assignment, $timeLeft, $unit = 'days')
    {
        $this->assignment = $assignment;
        $this->timeLeft   = $timeLeft;
        $this->unit       = $unit;
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
     * Human-readable time-left string.
     */
    private function timeLeftLabel(): string
    {
        if ($this->unit === 'minutes') {
            return $this->timeLeft . ' minute(s)';
        }
        return $this->timeLeft . ' day(s)';
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $type  = $this->assignment->type ?? 'Assignment';
        $label = $this->timeLeftLabel();

        return (new MailMessage)
            ->subject('⏰ Deadline Reminder: ' . $this->assignment->title)
            ->line('URGENT: Your ' . strtolower($type) . ' "' . $this->assignment->title . '" is due in ' . $label . '!')
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
        $type  = $this->assignment->type ?? 'Assignment';
        $label = $this->timeLeftLabel();

        return [
            'assignment_id' => $this->assignment->id,
            'title'         => '⏰ Deadline Reminder: ' . $this->assignment->title,
            'message'       => 'Your ' . strtolower($type) . ' is due in ' . $label . '!',
        ];
    }
}
