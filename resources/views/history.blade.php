@extends('layouts.app')
@section('title', 'История')

@section('content')
    <h2 class="text-2xl font-light mb-6">История партий</h2>

    {{-- Фильтры --}}
    <div class="flex flex-wrap gap-3 mb-6 text-xs">
        {{-- Результат --}}
        <div class="inline-flex bg-slate-900 rounded-xl p-1">
            <a href="{{ route('history') }}" class="px-3 py-1.5 rounded-lg transition
                      {{ !request('winner') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200' }}">
                все
            </a>
            @foreach (['X' => 'X', 'O' => 'O', 'draw' => 'ничья'] as $val => $label)
                <a href="{{ route('history', array_merge(request()->all(), ['winner' => $val])) }}"
                    class="px-3 py-1.5 rounded-lg transition
                              {{ request('winner') === $val ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Режим --}}
        <div class="inline-flex bg-slate-900 rounded-xl p-1">
            <a href="{{ route('history', array_merge(request()->except('mode'), [])) }}" class="px-3 py-1.5 rounded-lg transition
                      {{ !request('mode') ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200' }}">
                все
            </a>
            <a href="{{ route('history', array_merge(request()->all(), ['mode' => 'ai'])) }}" class="px-3 py-1.5 rounded-lg transition
                      {{ request('mode') === 'ai' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200' }}">
                против AI
            </a>
            <a href="{{ route('history', array_merge(request()->all(), ['mode' => 'pvp'])) }}" class="px-3 py-1.5 rounded-lg transition
                      {{ request('mode') === 'pvp' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-200' }}">
                два игрока
            </a>
        </div>
    </div>

    @if ($games->isEmpty())
        <p class="text-slate-500">Ничего не найдено.</p>
    @else
        <div class="space-y-2">
            @foreach ($games as $g)
                <a href="{{ route('replay', $g) }}"
                    class="flex justify-between items-center p-4 bg-slate-900 hover:bg-slate-800 rounded-lg transition">
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-slate-600 w-12">#{{ $g->id }}</span>

                        @if ($g->winner === 'draw')
                            <span class="text-lg font-light text-slate-400">=</span>
                            <span class="text-sm text-slate-500">ничья</span>
                        @else
                            <span class="text-lg font-light {{ $g->winner === 'X' ? 'text-blue-400' : 'text-red-400' }}">
                                {{ $g->winner }}
                            </span>
                            <span class="text-sm text-slate-500">победа</span>
                        @endif

                        <span class="text-xs text-slate-600">
                            {{ $g->mode === 'ai' ? 'против AI' : 'два игрока' }}
                        </span>
                    </div>
                    <span class="text-xs text-slate-600">{{ $g->created_at->format('d.m.Y H:i') }}</span>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $games->links() }}</div>
    @endif
@endsection