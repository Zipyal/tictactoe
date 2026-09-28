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
        $board = $request->session()->get('board', array_fill(0, 9, ' '));
        $finished = $request->session()->get('finished', false);
        $winner = TicTacToe::checkWinner($board);

        return view('game', compact('board', 'mode', 'finished', 'winner'));
    }

    /** Сделать ход */
    public function move(Request $request)
    {
        $cell = (int) $request->input('cell');
        $board = $request->session()->get('board', array_fill(0, 9, ' '));
        $mode = $request->session()->get('mode', 'ai');
        $moves = $request->session()->get('moves', []);
        $finished = $request->session()->get('finished', false);

        // Играет только если клетка свободна и партия не закончена
        if (!$finished && $board[$cell] === ' ') {
            $board[$cell] = 'X';
            $moves[] = "X:$cell";

            $winner = TicTacToe::checkWinner($board);

            // Ход компьютера, если режим ИИ и игра не окончена
            if ($winner === null && $mode === 'ai') {
                $ai = TicTacToe::aiMove($board);
                $board[$ai] = 'O';
                $moves[] = "O:$ai";
                $winner = TicTacToe::checkWinner($board);
            }

            if ($winner !== null) {
                $finished = true;
                Game::create([
                    'winner' => $winner,
                    'mode' => $mode,
                    'moves' => $moves,
                ]);
            }

            $request->session()->put(compact('board', 'moves', 'finished'));
        }

        return redirect()->route('game');
    }

    /** Новая игра */
    public function reset(Request $request)
    {
        $request->session()->forget(['board', 'moves', 'finished']);
        return redirect()->route('game');
    }

    /** Сменить режим */
    public function mode(Request $request, string $mode)
    {
        $request->session()->put('mode', $mode);
        $request->session()->forget(['board', 'moves', 'finished']);
        return redirect()->route('game');
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
            'X' => $raw['X'] ?? 0,
            'O' => $raw['O'] ?? 0,
            'draw' => $raw['draw'] ?? 0,
        ];
        $stats['total'] = array_sum($stats);
        return view('stats', compact('stats'));
    }
}