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

    /**
     * Анализ позиции: кто победил и какая линия.
     * Возвращает ['winner' => 'X'|'O'|'draw'|null, 'line' => [0,1,2]|[]]
     */
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

    /** Обёртки для обратной совместимости — на случай, если где-то ещё вызывается checkWinner */
    public static function checkWinner(array $b): ?string
    {
        return self::analyze($b)['winner'];
    }

    /** Свободные клетки */
    public static function emptyCells(array $b): array
    {
        return array_keys(array_filter($b, fn($c) => $c === ' '));
    }

    /**
     * Минимакс с альфа-бета отсечением.
     * Возвращает ['score' => int, 'move' => int].
     * Оценка: +10 — выигрывает O, -10 — выигрывает X, 0 — ничья.
     */
    public static function minimax(array $b, string $player, $alpha = -INF, $beta = INF): array
    {
        $result = self::analyze($b);

        if ($result['winner'] === 'O')
            return ['score' => 10, 'move' => -1];
        if ($result['winner'] === 'X')
            return ['score' => -10, 'move' => -1];
        if ($result['winner'] === 'draw')
            return ['score' => 0, 'move' => -1];

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

            if ($beta <= $alpha)
                break; // альфа-бета отсечение
        }

        return $best;
    }

    /**
     * Ход компьютера с учётом уровня сложности.
     * Уровни: 'easy', 'medium', 'hard'.
     */
    public static function aiMove(array $b, string $difficulty = 'hard'): int
    {
        $free = self::emptyCells($b);
        if (empty($free))
            return -1;

        // Easy — чистый рандом
        if ($difficulty === 'easy') {
            return $free[array_rand($free)];
        }

        // Medium — 50/50 между рандомом и минимаксом
        if ($difficulty === 'medium') {
            if (random_int(0, 1) === 0) {
                return $free[array_rand($free)];
            }
        }

        // Hard (и вторая половина medium) — минимакс
        return self::minimax($b, 'O')['move'];
    }

    /**
     * Оценка позиции для игрока X.
     * Используется для "разбора партии" — понять, был ли ход игрока оптимальным.
     * +10 — X выиграет при идеальной игре, -10 — проиграет, 0 — ничья.
     */
    public static function evaluateForX(array $b): int
    {
        return (int) self::minimax($b, 'X')['score'];
    }
}