<?php

namespace App\Console\Commands;

use App\Models\Task;
use Illuminate\Console\Command;
use App\Notifications\TaskDueReminderNotification;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat untuk tugas yang jatuh tempo dalam 24 jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tasks = Task::with('user')
            ->whereBetween('due_date', [now(), now()->addDay()])
            ->whereNull('completed_at')
            ->cursor();
        $count = 0;
        foreach ($tasks as $task) {
            $task->user->notify(new TaskDueReminderNotification($task));
            $this->info("Notifikasi terkirim untuk tugas: {$task->title} (ID: {$task->id})");
            $count++;
        }
        $this->info("Total notifikasi terkirim: {$count}");
        return self::SUCCESS;
    }
}
