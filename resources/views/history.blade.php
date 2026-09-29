@extends('layouts.app')
@section('title', 'История')

@section('content')
<h2 class="text-2xl font-light mb-6">История партий</h2>

@if ($games->isEmpty())
    <p class="text-slate-500">Пока не сыграно ни одной партии.</p>
@else
    <div class="space-y-2">
        @foreach ($games as $g)
            <a href="{{ route('replay', $g) }}"
               class="flex justify-between items-center p-4 bg-slate-900 hover:bg-slate-800 rounded-lg transition">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-500">#{{ $g->id }}</span>
                    @if ($g->winner === 'X') <span class="text-blue-400">✕ победили</span>
                    @elseif ($g->winner === 'O') <span class="text-red-400">○ победили</span>
                    @else <span class="text-yellow-400">ничья</span>
                    @endif
                    <span class="text-xs text-slate-600">{{ $g->mode === 'ai' ? 'против ИИ' : 'два игрока' }}</span>
                </div>
                <span class="text-sm text-slate-500">{{ $g->created_at->format('d.m.Y H:i') }}</span>
            </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $games->links() }}</div>
@endif
@endsection