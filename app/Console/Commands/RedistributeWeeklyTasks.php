<?php

namespace App\Console\Commands;

use App\Models\Colocation;
use App\Models\Task;
use Illuminate\Console\Command;

class RedistributeWeeklyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'colocclean:redistribute-weekly-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Redistribue les tâches non terminées entre les membres (rotation hebdomadaire)';

    /**
     * Execute the console command.
     * Rotation : pour chaque tâche en attente, on assigne au membre "suivant"
     * dans la liste des membres, pour éviter qu\'une personne refasse la même tâche deux semaines de suite.
     */
    public function handle(): int
    {
        $colocations = Colocation::with(['members', 'tasks' => fn ($q) => $q->where('status', Task::STATUS_PENDING)])->get();

        $totalRedistributed = 0;

        foreach ($colocations as $colocation) {
            $members = $colocation->members()->orderBy('user_id')->get();
            $pendingTasks = $colocation->tasks; // déjà filtré (pending) via with()

            if ($members->count() < 2) {
                continue;
            }
            if ($pendingTasks->isEmpty()) {
                continue;
            }

            $memberIds = $members->pluck('id')->values()->all();
            $n = count($memberIds);

            foreach ($pendingTasks as $task) {
                $currentIndex = array_search((int) $task->assigned_to, $memberIds, true);
                if ($currentIndex === false) {
                    // Assigné à quelqu'un qui n'est plus membre : assigner au premier
                    $task->update(['assigned_to' => $memberIds[0]]);
                    $totalRedistributed++;
                    continue;
                }
                $nextIndex = ($currentIndex + 1) % $n;
                $newAssigneeId = $memberIds[$nextIndex];
                if ($newAssigneeId !== (int) $task->assigned_to) {
                    $task->update(['assigned_to' => $newAssigneeId]);
                    $totalRedistributed++;
                }
            }
        }

        $this->info("Redistribution hebdomadaire terminée : {$totalRedistributed} tâche(s) réassignée(s).");

        return self::SUCCESS;
    }
}
