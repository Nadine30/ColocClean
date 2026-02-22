<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Task;
use App\Models\TaskTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskTemplateController extends Controller
{
    /**
     * Ajouter un modèle de tâche récurrente.
     */
    public function store(Request $request, Colocation $colocation)
    {
        if (! $colocation->hasMember($request->user())) {
            abort(403, 'Vous n\'avez pas accès à cette colocation.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $colocation->taskTemplates()->create(['title' => $validated['title']]);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Modèle de tâche ajouté.');
    }

    /**
     * Supprimer un modèle de tâche.
     */
    public function destroy(Request $request, Colocation $colocation, TaskTemplate $taskTemplate)
    {
        if (! $colocation->hasMember($request->user())) {
            abort(403, 'Vous n\'avez pas accès à cette colocation.');
        }

        if ($taskTemplate->colocation_id !== $colocation->id) {
            abort(404);
        }

        $taskTemplate->delete();

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Modèle supprimé.');
    }

    /**
     * Créer une tâche à partir d'un modèle (avec échéance et assignation).
     */
    public function createTask(Request $request, Colocation $colocation, TaskTemplate $taskTemplate)
    {
        if (! $colocation->hasMember($request->user())) {
            abort(403, 'Vous n\'avez pas accès à cette colocation.');
        }

        if ($taskTemplate->colocation_id !== $colocation->id) {
            abort(404);
        }

        $validated = $request->validate([
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'assigned_to' => ['required', Rule::in($colocation->members->pluck('id')->toArray())],
        ]);

        $colocation->tasks()->create([
            'title' => $taskTemplate->title,
            'due_date' => $validated['due_date'],
            'assigned_to' => $validated['assigned_to'],
            'status' => Task::STATUS_PENDING,
        ]);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Tâche « ' . $taskTemplate->title . ' » créée.');
    }

    /**
     * Générer les tâches de la semaine : une tâche par modèle, échéance vendredi, répartition en rotation.
     */
    public function generateWeek(Request $request, Colocation $colocation)
    {
        if (! $colocation->hasMember($request->user())) {
            abort(403, 'Vous n\'avez pas accès à cette colocation.');
        }

        $members = $colocation->members()->orderBy('user_id')->get();
        $templates = $colocation->taskTemplates;

        if ($members->isEmpty()) {
            return redirect()
                ->route('colocations.show', $colocation)
                ->with('error', 'Aucun membre dans la colocation.');
        }

        if ($templates->isEmpty()) {
            return redirect()
                ->route('colocations.show', $colocation)
                ->with('error', 'Aucun modèle de tâche. Ajoutez-en d\'abord.');
        }

        $nextFriday = Carbon::now()->next(Carbon::FRIDAY);
        if ($nextFriday->isPast() || $nextFriday->isToday()) {
            $nextFriday->addWeek();
        }

        $memberIds = $members->pluck('id')->values()->all();
        $n = count($memberIds);
        $created = 0;

        foreach ($templates as $i => $template) {
            $assigneeId = $memberIds[$i % $n];
            $colocation->tasks()->create([
                'title' => $template->title,
                'due_date' => $nextFriday,
                'assigned_to' => $assigneeId,
                'status' => Task::STATUS_PENDING,
            ]);
            $created++;
        }

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', $created . ' tâche(s) créée(s) pour la semaine (échéance ' . $nextFriday->format('d/m/Y') . ').');
    }
}
