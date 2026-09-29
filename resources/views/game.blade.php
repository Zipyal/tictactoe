@extends('layouts.app')

@section('title', 'Игра')

@section('content')
<div class="flex flex-col items-center">
    {{-- Переключатель режима --}}
    <div class="flex gap-2 mb-6">
        <a href="{{ route('mode', 'ai') }}"
           class="px-4 py-2 rounded-lg text-sm transition
                  {{ $mode === 'ai' ? 'bg-blue-600 text-white' : 'bg-slate-800 hover:bg-slate-700' }}">
            🤖 Против компьютера
        </a>
        <a href="{{ route('mode', 'pvp') }}"
           class="px-4 py-2 rounded-lg text-sm transition
                  {{ $mode === 'pvp' ? 'bg-blue-600 text-white' : 'bg-slate-800 hover:bg-slate-700' }}">
            👥 Два игрока
        </a>
    </div>

    {{-- Игровое поле --}}
    <form method="POST" action="{{ route('move') }}" class="grid grid-cols-3 gap-2 p-2 bg-slate-900 rounded-2xl">
        @csrf
        @foreach ($board as $i => $c)
            <button name="cell" value="{{ $i }}"
                    @disabled($c !== ' ' || $finished)
                    class="w-24 h-24 text-5xl font-bold rounded-xl transition
                           {{ $c === ' ' ? 'bg-slate-800 hover:bg-slate-700 cursor-pointer' : 'bg-slate-800/50 cursor-default' }}
                           {{ $c === 'X' ? 'text-blue-400' : ($c === 'O' ? 'text-red-400' : '') }}">
                {{ $c === ' ' ? '' : $c }}
            </button>
        @endforeach
    </form>

    {{-- Статус --}}
    <div class="mt-6 text-lg h-7">
        @if ($winner === 'X')
            <span class="text-green-400">🎉 Победили крестики!</span>
        @elseif ($winner === 'O')
            <span class="text-red-400">💀 Победили нолики</span>
        @elseif ($winner === 'draw')
            <span class="text-yellow-400">🤝 Ничья</span>
        @else
            <span class="text-slate-400">Ход: <b class="text-blue-400">X</b></span>
        @endif
    </div>

    {{-- Кнопка новой игры --}}
    <form method="POST" action="{{ route('reset') }}" class="mt-4">
        @csrf
        <button class="px-6 py-2 bg-blue-600 hover:bg-blue-500 rounded-lg font-medium transition">
            Новая игра
        </button>
    </form>
</div>
@endsection

<style>
    @keyframes pop-in {
        0%   { transform: scale(0) rotate(-45deg); opacity: 0; }
        60%  { transform: scale(1.15) rotate(5deg); opacity: 1; }
        100% { transform: scale(1) rotate(0); }
    }
    .cell-pop { animation: pop-in 0.25s ease-out; }

    @keyframes pulse-win {
        0%, 100% { background-color: rgba(74, 222, 128, 0.2); }
        50%      { background-color: rgba(74, 222, 128, 0.5); }
    }
    .cell-win { animation: pulse-win 1s ease-in-out infinite; }
</style>