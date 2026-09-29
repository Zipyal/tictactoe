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

        $result = TicTacToe::analyze($board);
        $winner = $result['winner'];
        $winningLine = $result['line'];

        return view('game', compact(
            'board', 'mode', 'difficulty', 'finished', 'winner', 'winningLine'
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

        // Играем только если клетка свободна и партия не закончена
        if (!$finished && $board[$cell] === ' ') {
            $board[$cell] = 'X';
            $moves[] = "X:$cell";

            // Оценка позиции после хода игрока — пригодится для разбора
            $analysis[] = [
                'move' => $cell,
                'eval' => TicTacToe::evaluateForX($board),
            ];

            $result = TicTacToe::analyze($board);
            $winner = $result['winner'];

            // Ход компьютера, если режим ИИ и игра не окончена
            if ($winner === null && $mode === 'ai') {
                $ai = TicTacToe::aiMove($board, $difficulty);
                $board[$ai] = 'O';
                $moves[] = "O:$ai";

                $result = TicTacToe::analyze($board);
                $winner = $result['winner'];
            }

            if ($winner !== null) {
                $finished = true;
                Game::create([
                    'winner' => $winner,
                    'mode'   => $mode,
                    'moves'  => $moves,
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
        $request->session()->forget(['board', 'moves', 'analysis', 'finished']);
        return redirect()->route('game');
    }

    /** Сменить режим */
    public function mode(Request $request, string $mode)
    {
        $request->session()->put('mode', $mode);
        $request->session()->forget(['board', 'moves', 'analysis', 'finished']);
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
    public function history()
    {
        $games = Game::latest()->paginate(10);
        return view('history', compact('games'));
    }

    /** Реплей партии */
    public function replay(Game $game)
    {
        return view('replay', compact('game'));
    }

    /** Статистика */
    public function stats()
    {
        $raw = Game::selectRaw('winner, COUNT(*) as c')->groupBy('winner')->pluck('c', 'winner');
        $stats = [
            'X'    => $raw['X'] ?? 0,
            'O'    => $raw['O'] ?? 0,
            'draw' => $raw['draw'] ?? 0,
        ];
        $stats['total'] = array_sum($stats);
        return view('stats', compact('stats'));
    }
}