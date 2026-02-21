@extends('layouts.app')

@section('title', 'Mes colocations — ' . config('app.name'))

@section('content')
    <div class="space-y-8">
        <h1 class="text-2xl font-semibold text-gray-900">Mes colocations</h1>

        {{-- Créer une colocation --}}
        <section id="creer" class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Créer une colocation</h2>
            <form action="{{ route('colocations.store') }}" method="POST" class="flex gap-3 flex-wrap">
                @csrf
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Nom de la colocation"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required maxlength="255">
                @error('name')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Créer
                </button>
            </form>
        </section>

        {{-- Rejoindre avec un code --}}
        <section class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Rejoindre une colocation</h2>
            <form action="{{ route('colocations.join') }}" method="POST" class="flex gap-3 flex-wrap items-start">
                @csrf
                <input type="text" name="invitation_code" value="{{ old('invitation_code') }}"
                    placeholder="Code d'invitation (6 caractères)"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase"
                    maxlength="6" pattern="[A-Za-z0-9]{6}" style="width: 12rem;">
                @error('invitation_code')
                    <span class="text-red-600 text-sm w-full">{{ $message }}</span>
                @enderror
                <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-900">
                    Rejoindre
                </button>
            </form>
        </section>

        {{-- Liste des colocations --}}
        <section class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Vos colocations</h2>
            @if ($colocations->isEmpty())
                <p class="text-gray-500">Vous n'êtes dans aucune colocation. Créez-en une ou rejoignez-en une avec un code.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($colocations as $colocation)
                        <li class="py-3 first:pt-0">
                            <a href="{{ route('colocations.show', $colocation) }}" class="flex items-center justify-between text-indigo-600 hover:text-indigo-800 font-medium">
                                <span>{{ $colocation->name }}</span>
                                <span class="text-gray-400 text-sm">
                                    @if ($colocation->owner_id === auth()->id())
                                        (créée par vous)
                                    @else
                                        créée par {{ $colocation->owner->name }}
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
@endsection
