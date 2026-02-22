@extends('layouts.app')

@section('title', $colocation->name . ' — ' . config('app.name'))

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('colocations.index') }}" class="text-sm text-gray-500 hover:text-gray-700 mb-2 inline-block">← Mes colocations</a>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $colocation->name }}</h1>
            </div>
        </div>

        {{-- Code d'invitation (visible par tous les membres) --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Code d'invitation</h2>
            <p class="text-gray-600 mb-2">Partagez ce code avec vos colocataires pour qu'ils rejoignent la colocation.</p>
            <p class="text-2xl font-mono font-bold tracking-wider text-indigo-600">{{ $colocation->invitation_code }}</p>
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
                        <li class="py-4 flex flex-wrap items-center gap-3 {{ $task->isDone() ? 'opacity-75' : '' }}">
                            <div class="flex-1 min-w-0">
                                <span class="font-medium {{ $task->isDone() ? 'line-through text-gray-500' : 'text-gray-900' }}">{{ $task->title }}</span>
                                <span class="text-gray-500 text-sm ml-2">échéance {{ $task->due_date->format('d/m/Y') }}</span>
                                <span class="text-gray-500 text-sm">— {{ $task->assignedTo->name }}</span>
                            </div>
                            <div class="flex items-center gap-2">
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
