<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Tic-Tac-Toe')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <nav class="border-b border-slate-800">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('game') }}" class="text-xl font-light tracking-widest">TIC-TAC-TOE</a>
            <div class="flex gap-1 text-sm">
                <a href="{{ route('game') }}"    class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('game') ? 'bg-slate-800' : '' }}">Игра</a>
                <a href="{{ route('history') }}" class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('history') ? 'bg-slate-800' : '' }}">История</a>
                <a href="{{ route('stats') }}"   class="px-3 py-1.5 rounded hover:bg-slate-800 {{ request()->routeIs('stats') ? 'bg-slate-800' : '' }}">Статистика</a>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 py-8">
        @yield('content')
    </main>
</body>
</html>