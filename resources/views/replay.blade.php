@extends('layouts.app')

@section('title', 'Реплей #' . $game->id)

@section('content')
    <div class="flex flex-col items-center select-none">

        <div class="text-xs uppercase tracking-[0.3em] text-slate-500 mb-2">
            партия #{{ $game->id }}
        </div>
        <div class="text-sm text-slate-400 mb-8">
            {{ $game->mode === 'ai' ? 'против компьютера' : 'два игрока' }}
            · {{ $game->created_at->format('d.m.Y H:i') }}
        </div>

        {{-- Поле --}}
        <div id="board" class="grid grid-cols-3 gap-2 p-2 bg-slate-900 rounded-2xl"></div>

        {{-- Статус --}}
        <div class="mt-8 h-20 flex flex-col items-center justify-center">
            <div id="status-symbol" class="text-5xl font-thin text-slate-300">·</div>
            <div id="status-label" class="mt-1 text-xs uppercase tracking-[0.3em] text-slate-500">
                ход 0 из {{ count($game->moves) }}
            </div>
        </div>

        {{-- Кнопки --}}
        <div class="mt-2 flex gap-2">
            <button onclick="step(-1)"
                class="px-5 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs uppercase tracking-widest text-slate-400 hover:text-slate-200 transition">
                назад
            </button>
            <button onclick="step(1)"
                class="px-5 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs uppercase tracking-widest text-slate-400 hover:text-slate-200 transition">
                вперёд
            </button>
            <button onclick="step(999)"
                class="px-5 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs uppercase tracking-widest text-slate-400 hover:text-slate-200 transition">
                в конец
            </button>
        </div>

        <a href="{{ route('history') }}"
            class="mt-6 text-xs uppercase tracking-[0.3em] text-slate-600 hover:text-slate-400 transition">
            ← к истории
        </a>
    </div>

    <style>
        @keyframes pop-in {
            0% {
                transform: scale(0.6);
                opacity: 0;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .cell-pop {
            animation: pop-in 0.18s ease-out;
        }
    </style>

    <script>
        const moves = @json($game->moves);
        const finalWinner = @json($game->winner);
        let index = 0;

        function render() {
            const board = Array(9).fill(' ');
            for (let i = 0; i < index; i++) {
                const [p, c] = moves[i].split(':');
                board[c] = p;
            }

            // Определяем победную линию, если реплей дошёл до конца
            let winningLine = [];
            if (index === moves.length && finalWinner !== 'draw') {
                const lines = [
                    [0, 1, 2], [3, 4, 5], [6, 7, 8],
                    [0, 3, 6], [1, 4, 7], [2, 5, 8],
                    [0, 4, 8], [2, 4, 6],
                ];
                for (const line of lines) {
                    const [a, b, c] = line;
                    if (board[a] !== ' ' && board[a] === board[b] && board[a] === board[c]) {
                        winningLine = line;
                        break;
                    }
                }
            }

            const el = document.getElementById('board');
            el.innerHTML = '';
            board.forEach((c, i) => {
                const d = document.createElement('div');
                d.className = 'w-24 h-24 flex items-center justify-center text-5xl font-light rounded-xl transition ' +
                    (c === 'X' ? 'text-blue-400 bg-slate-800' :
                        c === 'O' ? 'text-red-400 bg-slate-800' :
                            'bg-slate-800/40') +
                    (winningLine.includes(i) ? ' ring-2 ring-green-400/40' : '');
                d.textContent = c === ' ' ? '' : c;
                el.appendChild(d);
            });

            // Статус
            const symbolEl = document.getElementById('status-symbol');
            const labelEl = document.getElementById('status-label');

            if (index === 0) {
                symbolEl.textContent = '·';
                symbolEl.className = 'text-5xl font-thin text-slate-300';
                labelEl.textContent = 'начало партии';
            } else if (index === moves.length) {
                if (finalWinner === 'draw') {
                    symbolEl.textContent = '=';
                    symbolEl.className = 'text-5xl font-thin text-slate-400';
                    labelEl.textContent = 'ничья';
                } else {
                    symbolEl.textContent = finalWinner;
                    symbolEl.className = 'text-5xl font-thin ' +
                        (finalWinner === 'X' ? 'text-blue-400' : 'text-red-400');
                    labelEl.textContent = 'победа';
                }
            } else {
                const [lastPlayer] = moves[index - 1].split(':');
                symbolEl.textContent = lastPlayer;
                symbolEl.className = 'text-5xl font-thin ' +
                    (lastPlayer === 'X' ? 'text-blue-400' : 'text-red-400');
                labelEl.textContent = `ход ${index} из ${moves.length}`;
            }
        }

        function step(delta) {
            index = Math.max(0, Math.min(moves.length, index + delta));
            render();
        }

        // Стрелки клавиатуры
        document.addEventListener('keydown', e => {
            if (e.key === 'ArrowLeft') step(-1);
            if (e.key === 'ArrowRight') step(1);
        });

        render();
    </script>
@endsection