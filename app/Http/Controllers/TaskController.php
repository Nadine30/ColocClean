<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Créer une tâche dans la colocation.
     */
    public function store(Request $request, Colocation $colocation)
    {
        if (! $colocation->hasMember($request->user())) {
            abort(403, 'Vous n\'avez pas accès à cette colocation.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'assigned_to' => ['required', Rule::in($colocation->members->pluck('id')->toArray())],
        ]);

        $colocation->tasks()->create([
            'title' => $validated['title'],
            'due_date' => $validated['due_date'],
            'assigned_to' => $validated['assigned_to'],
            'status' => Task::STATUS_PENDING,
        ]);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Tâche créée.');
    }

    /**
     * Mettre à jour une tâche (statut ou assignation si pas encore faite).
     */
    public function update(Request $request, Colocation $colocation, Task $task)
    {
        if (! $colocation->hasMember($request->user())) {
            abort(403, 'Vous n\'avez pas accès à cette colocation.');
        }

        if ($task->colocation_id !== $colocation->id) {
            abort(404);
        }

        if ($request->has('status') && $request->status === Task::STATUS_DONE) {
            $task->update([
                'status' => Task::STATUS_DONE,
                'completed_at' => now(),
                'completed_by' => $request->user()->id,
            ]);
            return redirect()
                ->route('colocations.show', $colocation)
                ->with('success', 'Tâche marquée comme faite.');
        }

        // Réassignation uniquement si la tâche n'est pas encore "done"
        if (! $task->isDone() && $request->has('assigned_to')) {
            $request->validate([
                'assigned_to' => ['required', Rule::in($colocation->members->pluck('id')->toArray())],
            ]);
            $task->update(['assigned_to' => $request->assigned_to]);
            return redirect()
                ->route('colocations.show', $colocation)
                ->with('success', 'Tâche réassignée.');
        }

        return redirect()->route('colocations.show', $colocation);
    }
}
