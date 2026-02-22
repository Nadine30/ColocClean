@extends('layouts.app')

@section('title', 'Tableau de bord — ' . config('app.name'))

@section('content')
    <div class="space-y-8">
        <h1 class="text-2xl font-semibold text-gray-900">Tableau de bord</h1>

        {{-- Mes tâches à faire --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Mes tâches à faire</h2>
            @if ($myPendingTasks->isEmpty())
                <p class="text-gray-500">Aucune tâche vous est assignée pour le moment.</p>
                <a href="{{ route('colocations.index') }}" class="inline-block mt-2 text-indigo-600 hover:text-indigo-800 text-sm font-medium">Voir mes colocations →</a>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($myPendingTasks as $task)
                        <li class="py-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="font-medium text-gray-900">{{ $task->title }}</span>
                                <span class="text-gray-500 text-sm ml-2">{{ $task->colocation->name }}</span>
                                <span class="text-gray-400 text-sm block mt-0.5">Échéance : {{ $task->due_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('tasks.update', [$task->colocation, $task]) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="done">
                                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">Marquer comme fait</button>
                                </form>
                                <a href="{{ route('colocations.show', $task->colocation) }}" class="text-sm text-gray-500 hover:text-gray-700">Voir la colocation</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Mes colocations --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Mes colocations</h2>
            @if ($colocations->isEmpty())
                <p class="text-gray-500">Vous n'êtes dans aucune colocation.</p>
                <a href="{{ route('colocations.index') }}#creer" class="inline-flex items-center mt-3 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Créer une colocation</a>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($colocations as $colocation)
                        <li class="py-3 first:pt-0">
                            <a href="{{ route('colocations.show', $colocation) }}" class="flex items-center justify-between font-medium text-indigo-600 hover:text-indigo-800">
                                <span>{{ $colocation->name }}</span>
                                <span class="text-gray-400 text-sm">
                                    @if ($colocation->owner_id === auth()->id())
                                        créée par vous
                                    @else
                                        {{ $colocation->owner->name }}
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('colocations.index') }}" class="inline-block mt-3 text-sm text-gray-500 hover:text-gray-700">Gérer mes colocations →</a>
            @endif
        </div>
    </div>
@endsection
