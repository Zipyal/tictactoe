@extends('layouts.app')
@section('title', 'Реплей #' . $game->id)

@section('content')
<h2 class="text-2xl font-light mb-2">Реплей партии #{{ $game->id }}</h2>
<p class="text-slate-500 mb-6">
    {{ $game->mode === 'ai' ? 'Против компьютера' : 'Два игрока' }} ·
    {{ $game->created_at->format('d.m.Y H:i') }}
</p>

<div class="flex flex-col items-center">
    <div id="board" class="grid grid-cols-3 gap-2 p-2 bg-slate-900 rounded-2xl"></div>
    <div id="status" class="mt-6 text-lg h-7 text-slate-400"></div>
    <div class="mt-4 flex gap-2">
        <button onclick="step(-1)" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg">◀ Назад</button>
        <button onclick="step(1)"  class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-lg">Вперёд ▶</button>
    </div>
</div>

<script>
    const moves = @json($game->moves);
    let index = 0;

    function render() {
        const board = Array(9).fill(' ');
        for (let i = 0; i < index; i++) {
            const [p, c] = moves[i].split(':');
            board[c] = p;
        }

        const el = document.getElementById('board');
        el.innerHTML = '';
        board.forEach(c => {
            const d = document.createElement('div');
            d.className = 'w-20 h-20 flex items-center justify-center text-4xl font-bold bg-slate-800 rounded-xl ' +
                (c === 'X' ? 'text-blue-400' : c === 'O' ? 'text-red-400' : '');
            d.textContent = c === ' ' ? '' : c;
            el.appendChild(d);
        });

        document.getElementById('status').textContent = `Ход ${index} из ${moves.length}`;
    }

    function step(delta) {
        index = Math.max(0, Math.min(moves.length, index + delta));
        render();
    }

    render();
</script>
@endsection