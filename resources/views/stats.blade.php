@extends('layouts.app')
@section('title', 'Статистика')

@section('content')
<h2 class="text-2xl font-light mb-6">Статистика</h2>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    @foreach ([
        ['Побед X',  $stats['X'],    'text-blue-400'],
        ['Побед O',  $stats['O'],    'text-red-400'],
        ['Ничьих',   $stats['draw'], 'text-yellow-400'],
        ['Всего',    $stats['total'],'text-slate-300'],
    ] as [$label, $value, $color])
        <div class="p-6 bg-slate-900 rounded-2xl text-center">
            <div class="text-3xl font-bold {{ $color }}">{{ $value }}</div>
            <div class="text-sm text-slate-500 mt-2">{{ $label }}</div>
        </div>
    @endforeach
</div>
@endsection