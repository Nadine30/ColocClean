<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tableau de bord : tâches à faire + accès aux colocations.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $myPendingTasks = Task::with('colocation')
            ->where('assigned_to', $user->id)
            ->where('status', Task::STATUS_PENDING)
            ->orderBy('due_date')
            ->orderBy('created_at')
            ->get();

        $colocations = $user->colocations()->with('owner')->get();

        return view('dashboard', [
            'myPendingTasks' => $myPendingTasks,
            'colocations' => $colocations,
        ]);
    }
}
