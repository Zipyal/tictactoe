<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Services\TicTacToe;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /** Главная — игровое поле */
    public function index(Request $request)
    {
        $mode = $request->session()->get('mode', 'ai');
        $difficulty = $request->session()->get('difficulty', 'hard');
        $board = $request->session()->get('board', array_fill(0, 9, ' '));
        $finished = $request->session()->get('finished', false);
        $currentPlayer = $request->session()->get('currentPlayer', 'X'); // ← ВОТ ЭТО

        $result = TicTacToe::analyze($board);
        $winner = $result['winner'];
        $winningLine = $result['line'];

        return view('game', compact(
            'board',
            'mode',
            'difficulty',
            'finished',
            'winner',
            'winningLine',
            'currentPlayer'
        ));
    }

    /** Сделать ход */
    public function move(Request $request)
    {
        $cell = (int) $request->input('cell');
        $board = $request->session()->get('board', array_fill(0, 9, ' '));
        $mode = $request->session()->get('mode', 'ai');
        $difficulty = $request->session()->get('difficulty', 'hard');
        $moves = $request->session()->get('moves', []);
        $analysis = $request->session()->get('analysis', []);
        $finished = $request->session()->get('finished', false);

        $currentPlayer = $request->session()->get('currentPlayer', 'X');

        if (!$finished && $board[$cell] === ' ') {

            // === Ход текущего игрока ===
            $board[$cell] = $currentPlayer;
            $moves[] = "{$currentPlayer}:{$cell}";

            if ($currentPlayer === 'X') {
                $analysis[] = [
                    'move' => $cell,
                    'eval' => TicTacToe::evaluateForX($board),
                ];
            }

            $result = TicTacToe::analyze($board);
            $winner = $result['winner'];

            // === Ход компьютера — ТОЛЬКО если сейчас был ход игрока X ===
            if ($winner === null && $mode === 'ai' && $currentPlayer === 'X') {
                $ai = TicTacToe::aiMove($board, $difficulty);
                $board[$ai] = 'O';
                $moves[] = "O:$ai";

                $result = TicTacToe::analyze($board);
                $winner = $result['winner'];
                // currentPlayer остаётся 'X' — следующий ход снова игрока
            }

            // === PvP — переключаем игрока ===
            if ($winner === null && $mode === 'pvp') {
                $currentPlayer = $currentPlayer === 'X' ? 'O' : 'X';
                $request->session()->put('currentPlayer', $currentPlayer);
            }

            // === Финал ===
            if ($winner !== null) {
                $finished = true;
                Game::create([
                    'winner' => $winner,
                    'mode' => $mode,
                    'moves' => $moves,
                    'analysis' => $analysis,
                ]);
            }

            $request->session()->put(compact('board', 'moves', 'analysis', 'finished'));
        }

        return redirect()->route('game');
    }

    /** Новая игра */
    public function reset(Request $request)
    {
        $request->session()->forget(['board', 'moves', 'analysis', 'finished', 'currentPlayer']);
        return redirect()->route('game');
    }

    /** Сменить режим */
    public function mode(Request $request, string $mode)
    {
        $request->session()->put('mode', $mode);
        $request->session()->forget(['board', 'moves', 'analysis', 'finished', 'currentPlayer']);
        return redirect()->route('game');
    }

    /** Сменить уровень сложности */
    public function difficulty(Request $request, string $level)
    {
        $request->session()->put('difficulty', $level);
        return redirect()->route('game');
    }

    /** Подсказка — лучший ход для игрока X */
    public function hint(Request $request)
    {
        $board = $request->session()->get('board', array_fill(0, 9, ' '));
        $best = TicTacToe::minimax($board, 'X');
        return response()->json(['move' => $best['move']]);
    }

    /** История партий */
    public function history(Request $request)
    {
        $query = Game::query();

        // Фильтр по режиму
        if ($mode = $request->input('mode')) {
            $query->where('mode', $mode);
        }

        // Фильтр по результату
        if ($winner = $request->input('winner')) {
            $query->where('winner', $winner);
        }

        $games = $query->latest()->paginate(10)->withQueryString();

        return view('history', compact('games'));
    }

    /** Статистика */
    public function stats()
    {
        $raw = Game::selectRaw('winner, COUNT(*) as c')->groupBy('winner')->pluck('c', 'winner');
        $stats = [
            'X' => $raw['X'] ?? 0,
            'O' => $raw['O'] ?? 0,
            'draw' => $raw['draw'] ?? 0,
        ];
        $stats['total'] = array_sum($stats);
        return view('stats', compact('stats'));
    }
}