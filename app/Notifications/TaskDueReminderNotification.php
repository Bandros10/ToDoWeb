<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskDueReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
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
        return (new MailMessage)
            ->subject("[Pengingat] Tugas '{$this->task->title}' Jatuh Tempo")
            ->greeting("Halo {$notifiable->name}!")
            ->line("Tugas Anda akan jatuh tempo dalam 24 jam:")
            ->line("**{$this->task->title}**")
            ->line("Jatuh tempo: {$this->task->due_date->format('d F Y H:i')}")
            ->action('Lihat Tugas', route('tasks.show', $this->task))
            ->line('Terima kasih telah menggunakan aplikasi kami!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => $this->task->title,
            'due_date' => $this->task->due_date->toDateTimeString(),
            'message' => 'Tugas akan jatuh tempo dalam 24 jam'
        ];
    }
}
