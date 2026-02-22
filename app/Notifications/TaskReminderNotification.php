<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  \Illuminate\Support\Collection<int, Task>  $tasks
     */
    public function __construct(
        public \Illuminate\Support\Collection $tasks
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->tasks->count();
        $message = (new MailMessage)
            ->subject('ColocClean — Rappel : ' . $count . ' tâche(s) à faire')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Vous avez des tâches non terminées dans vos colocations :');

        foreach ($this->tasks->take(10) as $task) {
            $message->line('• ' . e($task->title) . ' — ' . $task->colocation->name . ' (échéance : ' . $task->due_date->format('d/m/Y') . ')');
        }

        if ($count > 10) {
            $message->line('… et ' . ($count - 10) . ' autre(s) tâche(s).');
        }

        $message->action('Voir mes colocations', url(route('colocations.index')))
            ->line('Merci d\'utiliser ColocClean !');

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $taskList = $this->tasks->map(fn (Task $task) => [
            'id' => $task->id,
            'title' => $task->title,
            'due_date' => $task->due_date->format('d/m/Y'),
            'colocation_id' => $task->colocation_id,
            'colocation_name' => $task->colocation->name,
        ])->toArray();

        return [
            'message' => 'Vous avez ' . $this->tasks->count() . ' tâche(s) non terminée(s).',
            'tasks' => $taskList,
            'url' => route('colocations.index'),
        ];
    }
}
