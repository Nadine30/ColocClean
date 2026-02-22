<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskReminderNotification;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'colocclean:send-task-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie les rappels quotidiens (in-app + email) pour les tâches non terminées';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $pending = Task::with('colocation')
            ->where('status', 'pending')
            ->get()
            ->groupBy('assigned_to');

        $sent = 0;
        foreach ($pending as $userId => $tasks) {
            $user = $tasks->first()->assignedTo;
            if (! $user) {
                continue;
            }
            $user->notify(new TaskReminderNotification($tasks));
            $sent++;
        }

        $this->info("Rappels envoyés à {$sent} utilisateur(s).");

        return self::SUCCESS;
    }
}
