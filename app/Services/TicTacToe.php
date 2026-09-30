<?php

namespace App\Services;

class TicTacToe
{
    /** Все победные линии */
    private const LINES = [
        [0, 1, 2],
        [3, 4, 5],
        [6, 7, 8],
        [0, 3, 6],
        [1, 4, 7],
        [2, 5, 8],
        [0, 4, 8],
        [2, 4, 6],
    ];

    public static function analyze(array $b): array
    {
        foreach (self::LINES as $line) {
            [$a, $c, $d] = $line;
            if ($b[$a] !== ' ' && $b[$a] === $b[$c] && $b[$a] === $b[$d]) {
                return ['winner' => $b[$a], 'line' => $line];
            }
        }
        return [
            'winner' => in_array(' ', $b) ? null : 'draw',
            'line' => [],
        ];
    }

    public static function checkWinner(array $b): ?string
    {
        return self::analyze($b)['winner'];
    }

    public static function emptyCells(array $b): array
    {
        return array_keys(array_filter($b, fn($c) => $c === ' '));
    }

    public static function minimax(array $b, string $player, $alpha = -INF, $beta = INF): array
    {
        $result = self::analyze($b);

        if ($result['winner'] === 'O') return ['score' => 10, 'move' => -1];
        if ($result['winner'] === 'X') return ['score' => -10, 'move' => -1];
        if ($result['winner'] === 'draw') return ['score' => 0, 'move' => -1];

        $best = ['score' => $player === 'O' ? -INF : INF, 'move' => -1];

        foreach (self::emptyCells($b) as $i) {
            $b[$i] = $player;
            $res = self::minimax($b, $player === 'O' ? 'X' : 'O', $alpha, $beta);
            $b[$i] = ' ';

            if ($player === 'O') {
                if ($res['score'] > $best['score']) {
                    $best = ['score' => $res['score'], 'move' => $i];
                }
                $alpha = max($alpha, $best['score']);
            } else {
                if ($res['score'] < $best['score']) {
                    $best = ['score' => $res['score'], 'move' => $i];
                }
                $beta = min($beta, $best['score']);
            }

            if ($beta <= $alpha) break;
        }

        return $best;
    }

    public static function aiMove(array $b, string $difficulty = 'hard'): int
    {
        $free = self::emptyCells($b);
        if (empty($free)) return -1;

        if ($difficulty === 'easy') {
            return $free[array_rand($free)];
        }

        if ($difficulty === 'medium') {
            if (random_int(0, 1) === 0) {
                return $free[array_rand($free)];
            }
        }

        return self::minimax($b, 'O')['move'];
    }

    public static function evaluateForX(array $b): int
    {
        return (int) self::minimax($b, 'X')['score'];
    }

    /**
     * Аннотирует анализ партии: помечает ходы игрока как хорошие/ошибки.
     */
    public static function annotateAnalysis(array $analysis): array
    {
        $prevEval = null;
        foreach ($analysis as &$item) {
            if ($prevEval === null) {
                $item['good'] = true;
                $prevEval = $item['eval'];
                continue;
            }
            $item['good'] = $item['eval'] >= $prevEval - 1;
            $prevEval = $item['eval'];
        }
        unset($item);

        return $analysis;
    }
}