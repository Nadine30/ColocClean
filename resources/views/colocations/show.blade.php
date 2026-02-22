@extends('layouts.app')

@section('title', $colocation->name . ' — ' . config('app.name'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div>
                <a href="{{ route('colocations.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-block">← Mes colocations</a>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $colocation->name }}</h1>
            </div>
            <form action="{{ route('colocations.leave', $colocation) }}" method="POST" class="inline" onsubmit="return confirm('Quitter cette colocation ? Vous ne pourrez plus accéder à ses tâches.');">
                @csrf
                <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Quitter la colocation</button>
            </form>
        </div>

        {{-- Code d'invitation (visible par tous les membres) --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Code d'invitation</h2>
            <p class="text-gray-600 mb-2">Partagez ce code avec vos colocataires pour qu'ils rejoignent la colocation.</p>
            <p class="text-2xl font-mono font-bold tracking-wider text-indigo-600">{{ $colocation->invitation_code }}</p>
        </div>

        {{-- Modèles de tâches récurrentes --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Modèles de tâches récurrentes</h2>
            <p class="text-gray-500 text-sm mb-4">Définissez des tâches types pour les recréer rapidement (ex. « Sortir les poubelles », « Nettoyer la salle de bain »).</p>

            <form action="{{ route('task-templates.store', $colocation) }}" method="POST" class="flex gap-2 mb-4">
                @csrf
                <input type="text" name="title" value="{{ old('title') }}" placeholder="Ex: Sortir les poubelles" required maxlength="255"
                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="rounded-md bg-gray-700 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">Ajouter le modèle</button>
            </form>
            @error('title')
                <p class="text-red-600 text-sm mb-2">{{ $message }}</p>
            @enderror

            @if ($colocation->taskTemplates->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <form action="{{ route('task-templates.generate-week', $colocation) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">
                            Générer les tâches de la semaine
                        </button>
                    </form>
                    <span class="text-gray-500 text-sm">→ une tâche par modèle, échéance vendredi, répartition entre les membres</span>
                </div>
                <ul class="divide-y divide-gray-200">
                    @foreach ($colocation->taskTemplates as $template)
                        <li class="py-3 flex flex-wrap items-center gap-3">
                            <span class="font-medium text-gray-900">{{ $template->title }}</span>
                            <form action="{{ route('task-templates.create-task', [$colocation, $template]) }}" method="POST" class="inline-flex flex-wrap items-center gap-2">
                                @csrf
                                <input type="date" name="due_date" value="{{ \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}" min="{{ date('Y-m-d') }}"
                                    class="rounded-md border-gray-300 shadow-sm text-sm">
                                <select name="assigned_to" class="rounded-md border-gray-300 shadow-sm text-sm py-0.5" required>
                                    @foreach ($colocation->members as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Créer une tâche</button>
                            </form>
                            <form action="{{ route('task-templates.destroy', [$colocation, $template]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer ce modèle ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Supprimer</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-gray-500 text-sm">Aucun modèle. Ajoutez-en un ci-dessus pour pouvoir générer les tâches de la semaine en un clic.</p>
            @endif
        </div>

        {{-- Tâches --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Tâches</h2>

            <form action="{{ route('tasks.store', $colocation) }}" method="POST" class="mb-6 p-4 bg-gray-50 rounded-lg">
                @csrf
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Titre</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required maxlength="255"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Ex: Nettoyer la salle de bain">
                        @error('title')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700">Échéance</label>
                        <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" required
                            min="{{ date('Y-m-d') }}"
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('due_date')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="assigned_to" class="block text-sm font-medium text-gray-700">Attribuée à</label>
                        <select name="assigned_to" id="assigned_to" required
                            class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach ($colocation->members as $member)
                                <option value="{{ $member->id }}" {{ old('assigned_to') == $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <span class="text-red-600 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Ajouter la tâche
                    </button>
                </div>
            </form>

            @if ($colocation->tasks->isEmpty())
                <p class="text-gray-500">Aucune tâche pour le moment. Ajoutez-en une ci-dessus.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($colocation->tasks as $task)
                        <li class="py-4 {{ $task->isDone() ? 'opacity-75' : '' }}">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex-1 min-w-0">
                                    <span class="font-medium {{ $task->isDone() ? 'line-through text-gray-500' : 'text-gray-900' }}">{{ $task->title }}</span>
                                    <span class="text-gray-500 text-sm ml-2">échéance {{ $task->due_date->format('d/m/Y') }}</span>
                                    <span class="text-gray-500 text-sm">— {{ $task->assignedTo->name }}</span>
                                </div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if ($task->isDone())
                                        <span class="text-sm text-green-600 font-medium">
                                            Fait
                                            @if ($task->completed_at && $task->completedBy)
                                                par {{ $task->completedBy->name }} le {{ $task->completed_at->format('d/m/Y à H:i') }}
                                            @endif
                                        </span>
                                    @else
                                        <form action="{{ route('tasks.update', [$colocation, $task]) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="done">
                                            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Marquer comme fait</button>
                                        </form>
                                        <form action="{{ route('tasks.update', [$colocation, $task]) }}" method="POST" class="inline flex items-center gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <select name="assigned_to" class="text-sm rounded border-gray-300 py-0.5" onchange="this.form.submit()">
                                                @foreach ($colocation->members as $member)
                                                    <option value="{{ $member->id }}" {{ $task->assigned_to === $member->id ? 'selected' : '' }}>{{ $member->name }}</option>
                                                @endforeach
                                            </select>
                                            <span class="text-xs text-gray-500">Réassigner</span>
                                        </form>
                                    @endif
                                    <form action="{{ route('tasks.destroy', [$colocation, $task]) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette tâche ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-gray-500 hover:text-red-600">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                            {{-- Commentaires --}}
                            <div class="mt-3 pl-2 border-l-2 border-gray-100">
                                @if ($task->comments->isNotEmpty())
                                    <ul class="space-y-2 mb-3">
                                        @foreach ($task->comments as $comment)
                                            <li class="text-sm text-gray-600">
                                                <span class="font-medium text-gray-800">{{ $comment->user->name }}</span>
                                                <span class="text-gray-400 text-xs">{{ $comment->created_at->format('d/m/Y H:i') }}</span>
                                                <p class="mt-0.5">{{ nl2br(e($comment->body)) }}</p>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                <form action="{{ route('task-comments.store', [$colocation, $task]) }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="body" value="{{ old('body', '') }}" placeholder="Ajouter un commentaire…" required maxlength="2000"
                                        class="flex-1 rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <button type="submit" class="rounded-md bg-gray-600 px-3 py-1.5 text-sm text-white hover:bg-gray-700">Envoyer</button>
                                </form>
                                @error('body')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Historique (tâches effectuées) --}}
        @php
            $completedTasks = $colocation->tasks->where('status', 'done')->sortByDesc(fn ($t) => $t->completed_at?->getTimestamp() ?? 0)->values();
        @endphp
        @if ($completedTasks->isNotEmpty())
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Historique</h2>
                <p class="text-gray-500 text-sm mb-4">Qui a effectué quelle tâche et à quelle date.</p>
                <ul class="divide-y divide-gray-200">
                    @foreach ($completedTasks as $task)
                        <li class="py-3 flex flex-wrap items-baseline gap-2">
                            <span class="font-medium text-gray-900">{{ $task->title }}</span>
                            <span class="text-gray-500 text-sm">
                                @if ($task->completed_at && $task->completedBy)
                                    — Fait par <strong>{{ $task->completedBy->name }}</strong> le {{ $task->completed_at->format('d/m/Y à H:i') }}
                                @else
                                    — Fait
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Membres --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Membres</h2>
            <ul class="divide-y divide-gray-200">
                @foreach ($colocation->members as $member)
                    <li class="py-2 flex items-center justify-between">
                        <span>{{ $member->name }}</span>
                        @if ($colocation->owner_id === $member->id)
                            <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded">Créateur</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endsection
