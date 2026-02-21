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
