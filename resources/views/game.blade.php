@extends('layouts.app')

@section('title', 'Игра')

@section('content')
    <div class="flex flex-col items-center select-none">

        {{-- Режим: сегментированный переключатель --}}
        <div class="inline-flex bg-slate-900 rounded-xl p-1 mb-3 text-sm">
            <a href="{{ route('mode', 'ai') }}" class="px-5 py-2 rounded-lg transition
                      {{ $mode === 'ai' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200' }}">
                один
            </a>
            <a href="{{ route('mode', 'pvp') }}" class="px-5 py-2 rounded-lg transition
                      {{ $mode === 'pvp' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200' }}">
                вдвоём
            </a>
        </div>

        {{-- Сложность --}}
        @if ($mode === 'ai')
            <div class="inline-flex bg-slate-900 rounded-xl p-1 mb-6 text-xs">
                @foreach (['easy' => 'легко', 'medium' => 'средне', 'hard' => 'сложно'] as $level => $label)
                    <a href="{{ route('difficulty', $level) }}"
                        class="px-4 py-1.5 rounded-lg transition
                                      {{ $difficulty === $level ? 'bg-slate-700 text-white' : 'text-slate-500 hover:text-slate-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Поле --}}
        <form method="POST" action="{{ route('move') }}" class="grid grid-cols-3 gap-2 p-2 bg-slate-900 rounded-2xl">
            @csrf
            @foreach ($board as $i => $c)
                <button name="cell" value="{{ $i }}" @disabled($c !== ' ' || $finished) class="cell w-24 h-24 text-5xl font-light rounded-xl transition
                                   {{ $c === ' ' ? 'bg-slate-800 hover:bg-slate-700 cursor-pointer' : 'bg-slate-800/40 cursor-default' }}
                                   {{ $c === 'X' ? 'text-blue-400' : ($c === 'O' ? 'text-red-400' : '') }}
                                   {{ $c !== ' ' ? 'cell-pop' : '' }}
                                   {{ in_array($i, $winningLine) ? 'cell-win' : '' }}">
                    {{ $c === ' ' ? '' : $c }}
                </button>
            @endforeach
        </form>

        {{-- Статус: символ + короткое слово --}}
        @php
            $playerWon = ($mode === 'pvp' && in_array($winner, ['X', 'O']))
                || ($mode === 'ai' && $winner === 'X');
            $aiWon = ($mode === 'ai' && $winner === 'O');
        @endphp

        <div class="mt-8 h-20 flex flex-col items-center justify-center">
            @if ($winner === 'draw')
                <div class="text-2xl font-light tracking-widest text-slate-400">ничья</div>
            @elseif ($playerWon)
                <div class="text-5xl font-thin {{ $winner === 'X' ? 'text-blue-400' : 'text-red-400' }}">
                    {{ $winner }}
                </div>
                <div class="mt-1 text-xs uppercase tracking-[0.3em] text-slate-500">победа</div>
            @elseif ($aiWon)
                <div class="text-5xl font-thin text-red-400">O</div>
                <div class="mt-1 text-xs uppercase tracking-[0.3em] text-slate-500">поражение</div>
            @else
                <div class="text-5xl font-thin {{ $currentPlayer === 'X' ? 'text-blue-400' : 'text-red-400' }}">
                    {{ $currentPlayer }}
                </div>
                <div class="mt-1 text-xs uppercase tracking-[0.3em] text-slate-500">
                    {{ $mode === 'ai' ? 'твой ход' : ($currentPlayer === 'X' ? 'ход первого' : 'ход второго') }}
                </div>
            @endif
        </div>

        {{-- Кнопки --}}
        <div class="mt-2 flex gap-2">
            @if (!$finished && $mode === 'ai')
                <button type="button" onclick="getHint()"
                    class="px-5 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs uppercase tracking-widest text-slate-400 hover:text-slate-200 transition">
                    Подсказка
                </button>
            @endif
            <form method="POST" action="{{ route('reset') }}">
                @csrf
                <button
                    class="px-5 py-2 bg-slate-900 hover:bg-slate-800 rounded-lg text-xs uppercase tracking-widest text-slate-400 hover:text-slate-200 transition">
                    заново
                </button>
            </form>
        </div>
    </div>

    <canvas id="confetti" class="fixed inset-0 pointer-events-none z-50"></canvas>

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

        @keyframes pulse-win {

            0%,
            100% {
                background-color: rgba(74, 222, 128, 0.10);
            }

            50% {
                background-color: rgba(74, 222, 128, 0.30);
            }
        }

        .cell-win {
            animation: pulse-win 1.6s ease-in-out infinite;
        }
    </style>

    <script>
        // === Звуки ===
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        function beep(freq, duration, type = 'sine', volume = 0.1) {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = type;
            osc.frequency.value = freq;
            gain.gain.value = volume;
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.start();
            gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + duration);
            osc.stop(audioCtx.currentTime + duration);
        }

        document.querySelectorAll('button[name="cell"]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!btn.disabled) beep(600, 0.06, 'square', 0.05);
            });
        });

        @if ($finished)
            @if ($playerWon)
                beep(880, 0.12); setTimeout(() => beep(1100, 0.12), 130);
                setTimeout(() => beep(1320, 0.25), 260);
            @elseif ($aiWon)
                beep(400, 0.18, 'sawtooth', 0.06);
                setTimeout(() => beep(280, 0.25, 'sawtooth', 0.06), 180);
            @elseif ($winner === 'draw')
                beep(500, 0.12); setTimeout(() => beep(500, 0.12), 180);
            @endif
        @endif

        // === Конфетти: и в PvP, и при победе игрока в AI ===
        @if ($finished && $playerWon)
            (function launchConfetti() {
                const canvas = document.getElementById('confetti');
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                const ctx = canvas.getContext('2d');
                const colors = ['#60a5fa', '#f87171', '#facc15', '#4ade80', '#c084fc'];
                const pieces = Array.from({length: 140}, () => ({
                    x: Math.random() * canvas.width,
                    y: -20 - Math.random() * 200,
                    vx: (Math.random() - 0.5) * 4,
                    vy: Math.random() * 3 + 2,
                    size: Math.random() * 6 + 3,
                    color: colors[Math.floor(Math.random() * colors.length)],
                    rot: Math.random() * 360,
                    rotSpeed: (Math.random() - 0.5) * 12,
                }));
                let frames = 0;
                function draw() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    pieces.forEach(p => {
                        p.x += p.vx; p.y += p.vy; p.rot += p.rotSpeed;
                        ctx.save();
                        ctx.translate(p.x, p.y);
                        ctx.rotate(p.rot * Math.PI / 180);
                        ctx.fillStyle = p.color;
                        ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size);
                        ctx.restore();
                    });
                    frames++;
                    if (frames < 300) requestAnimationFrame(draw);
                    else ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
                draw();
            })();
        @endif

        // === Подсказка ===
        async function getHint() {
            try {
                const res = await fetch('{{ route('hint') }}', {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}
                });
                const data = await res.json();
                if (data.move === undefined || data.move < 0) return;
                const cell = document.querySelector(`button[name="cell"][value="${data.move}"]`);
                if (cell && !cell.disabled) {
                    cell.classList.add('ring-2', 'ring-yellow-400/60');
                    beep(900, 0.08, 'sine', 0.04);
                    setTimeout(() => cell.classList.remove('ring-2', 'ring-yellow-400/60'), 1400);
                }
            } catch (e) {console.error('Hint error', e);}
        }
    </script>
@endsection