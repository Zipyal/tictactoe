@extends('layouts.app')

@section('title', 'Игра')

@section('content')
    <div class="flex flex-col items-center">

        {{-- Переключатель режима --}}
        <div class="flex gap-2 mb-3">
            <a href="{{ route('mode', 'ai') }}" class="px-4 py-2 rounded-lg text-sm transition
                      {{ $mode === 'ai' ? 'bg-blue-600 text-white' : 'bg-slate-800 hover:bg-slate-700' }}">
                🤖 Против компьютера
            </a>
            <a href="{{ route('mode', 'pvp') }}" class="px-4 py-2 rounded-lg text-sm transition
                      {{ $mode === 'pvp' ? 'bg-blue-600 text-white' : 'bg-slate-800 hover:bg-slate-700' }}">
                👥 Два игрока
            </a>
        </div>

        {{-- Переключатель сложности (только для режима AI) --}}
        @if ($mode === 'ai')
            <div class="flex gap-2 mb-6">
                @foreach (['easy' => '😴 Легко', 'medium' => '🙂 Средне', 'hard' => '😈 Сложно'] as $level => $label)
                    <a href="{{ route('difficulty', $level) }}" class="px-3 py-1.5 rounded-lg text-xs transition
                                      {{ $difficulty === $level ? 'bg-blue-600 text-white' : 'bg-slate-800 hover:bg-slate-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Игровое поле --}}
        <form method="POST" action="{{ route('move') }}" class="grid grid-cols-3 gap-2 p-2 bg-slate-900 rounded-2xl">
            @csrf
            @foreach ($board as $i => $c)
                <button name="cell" value="{{ $i }}" @disabled($c !== ' ' || $finished) class="cell w-24 h-24 text-5xl font-bold rounded-xl transition
                                   {{ $c === ' ' ? 'bg-slate-800 hover:bg-slate-700 cursor-pointer' : 'bg-slate-800/50 cursor-default' }}
                                   {{ $c === 'X' ? 'text-blue-400' : ($c === 'O' ? 'text-red-400' : '') }}
                                   {{ $c !== ' ' ? 'cell-pop' : '' }}
                                   {{ in_array($i, $winningLine) ? 'cell-win' : '' }}">
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
                <span class="text-slate-400">
                    Ход: <b class="{{ $mode === 'ai' ? 'text-blue-400' : 'text-blue-400' }}">X</b>
                </span>
            @endif
        </div>

        {{-- Кнопки --}}
        <div class="mt-4 flex gap-2">
            @if (!$finished && $mode === 'ai')
                <button type="button" onclick="getHint()"
                    class="px-5 py-2 bg-slate-800 hover:bg-slate-700 rounded-lg text-sm transition">
                    💡 Подсказать
                </button>
            @endif
            <form method="POST" action="{{ route('reset') }}">
                @csrf
                <button class="px-6 py-2 bg-blue-600 hover:bg-blue-500 rounded-lg font-medium transition">
                    Новая игра
                </button>
            </form>
        </div>
    </div>

    {{-- Конфетти-канвас --}}
    <canvas id="confetti" class="fixed inset-0 pointer-events-none z-50"></canvas>

    <style>
        @keyframes pop-in {
            0% {
                transform: scale(0) rotate(-45deg);
                opacity: 0;
            }

            60% {
                transform: scale(1.15) rotate(5deg);
                opacity: 1;
            }

            100% {
                transform: scale(1) rotate(0);
            }
        }

        .cell-pop {
            animation: pop-in 0.25s ease-out;
        }

        @keyframes pulse-win {

            0%,
            100% {
                background-color: rgba(74, 222, 128, 0.25);
                box-shadow: 0 0 0 rgba(74, 222, 128, 0);
            }

            50% {
                background-color: rgba(74, 222, 128, 0.55);
                box-shadow: 0 0 20px rgba(74, 222, 128, 0.6);
            }
        }

        .cell-win {
            animation: pulse-win 1s ease-in-out infinite;
        }
    </style>

    <script>
        // === Звуки через Web Audio API ===
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

        // Щелчок при клике на клетку
        document.querySelectorAll('button[name="cell"]').forEach(btn => {
            btn.addEventListener('click', () => {
                if (!btn.disabled) beep(600, 0.08, 'square', 0.06);
            });
        });

        // Звук финала
        @if ($finished)
            @if ($winner === 'X')
                beep(880, 0.15); setTimeout(() => beep(1100, 0.15), 150);
                setTimeout(() => beep(1320, 0.3), 300);
            @elseif ($winner === 'O')
                beep(400, 0.2, 'sawtooth', 0.08);
                setTimeout(() => beep(280, 0.3, 'sawtooth', 0.08), 200);
            @elseif ($winner === 'draw')
                beep(500, 0.15); setTimeout(() => beep(500, 0.15), 200);
            @endif
        @endif

        // === Конфетти при победе игрока ===
        @if ($finished && $winner === 'X')
            (function launchConfetti() {
                const canvas = document.getElementById('confetti');
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
                const ctx = canvas.getContext('2d');
                const colors = ['#4da3ff', '#ff6b6b', '#ffd93d', '#4dff88', '#c084fc'];
                const pieces = Array.from({length: 120}, () => ({
                    x: Math.random() * canvas.width,
                    y: -20 - Math.random() * 200,
                    vx: (Math.random() - 0.5) * 4,
                    vy: Math.random() * 3 + 2,
                    size: Math.random() * 8 + 4,
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
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const data = await res.json();
                if (data.move === undefined || data.move < 0) return;
                const cell = document.querySelector(`button[name="cell"][value="${data.move}"]`);
                if (cell && !cell.disabled) {
                    cell.classList.add('ring-4', 'ring-yellow-400');
                    beep(900, 0.1, 'sine', 0.05);
                    setTimeout(() => cell.classList.remove('ring-4', 'ring-yellow-400'), 1500);
                }
            } catch (e) {
                console.error('Hint error', e);
            }
        }
    </script>
@endsection