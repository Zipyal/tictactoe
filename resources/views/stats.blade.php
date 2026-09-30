@extends('layouts.app')
@section('title', 'Статистика')

@section('content')
    <h2 class="text-2xl font-light mb-8">Статистика</h2>

    {{-- Основные счётчики --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="p-6 bg-slate-900 rounded-2xl text-center">
            <div class="text-3xl font-light text-blue-400">{{ $stats['X'] }}</div>
            <div class="text-xs uppercase tracking-[0.2em] text-slate-500 mt-2">победы X</div>
            <div class="text-xs text-slate-600 mt-1">{{ $stats['win_rate'] }}%</div>
        </div>

        <div class="p-6 bg-slate-900 rounded-2xl text-center">
            <div class="text-3xl font-light text-red-400">{{ $stats['O'] }}</div>
            <div class="text-xs uppercase tracking-[0.2em] text-slate-500 mt-2">победы O</div>
            <div class="text-xs text-slate-600 mt-1">{{ $stats['lose_rate'] }}%</div>
        </div>

        <div class="p-6 bg-slate-900 rounded-2xl text-center">
            <div class="text-3xl font-light text-slate-300">{{ $stats['draw'] }}</div>
            <div class="text-xs uppercase tracking-[0.2em] text-slate-500 mt-2">ничьи</div>
            <div class="text-xs text-slate-600 mt-1">{{ $stats['draw_rate'] }}%</div>
        </div>

        <div class="p-6 bg-slate-900 rounded-2xl text-center">
            <div class="text-3xl font-light text-slate-300">{{ $stats['total'] }}</div>
            <div class="text-xs uppercase tracking-[0.2em] text-slate-500 mt-2">всего</div>
            <div class="text-xs text-slate-600 mt-1">средняя {{ $stats['avg_moves'] }} ходов</div>
        </div>
    </div>

    {{-- Последняя партия --}}
    @if ($stats['last_game'])
        <div class="text-xs uppercase tracking-[0.3em] text-slate-600 mb-3">последняя партия</div>
        <a href="{{ route('replay', $stats['last_game']) }}"
            class="block p-4 bg-slate-900 hover:bg-slate-800 rounded-lg transition">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    @if ($stats['last_game']->winner === 'draw')
                        <span class="text-lg font-light text-slate-400">=</span>
                        <span class="text-sm text-slate-500">ничья</span>
                    @else
                        <span
                            class="text-lg font-light {{ $stats['last_game']->winner === 'X' ? 'text-blue-400' : 'text-red-400' }}">
                            {{ $stats['last_game']->winner }}
                        </span>
                        <span class="text-sm text-slate-500">победа</span>
                    @endif
                    <span class="text-xs text-slate-600">
                        {{ $stats['last_game']->mode === 'ai' ? 'против AI' : 'два игрока' }}
                    </span>
                </div>
                <span class="text-xs text-slate-600">
                    {{ $stats['last_game']->created_at->format('d.m.Y H:i') }}
                </span>
            </div>
        </a>
    @endif
@endsection