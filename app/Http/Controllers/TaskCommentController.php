<?php

namespace App\Http\Controllers;

use App\Models\Colocation;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /**
     * Ajouter un commentaire sur une tâche.
     */
    public function store(Request $request, Colocation $colocation, Task $task)
    {
        if (! $colocation->hasMember($request->user())) {
            abort(403, 'Vous n\'avez pas accès à cette colocation.');
        }

        if ($task->colocation_id !== $colocation->id) {
            abort(404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return redirect()
            ->route('colocations.show', $colocation)
            ->with('success', 'Commentaire ajouté.');
    }
}
