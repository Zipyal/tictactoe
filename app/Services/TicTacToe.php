<?php

namespace App\Services;

class TicTacToe
{
    /** Проверка победителя. Возврат 'X', 'O', 'draw' или null. */
    public static function checkWinner(array $b): ?string
    {
        $lines = [
            [0,1,2],[3,4,5],[6,7,8],
            [0,3,6],[1,4,7],[2,5,8],
            [0,4,8],[2,4,6],
        ];
        foreach ($lines as [$a,$c,$d]) {
            if ($b[$a] !== ' ' && $b[$a] === $b[$c] && $b[$a] === $b[$d]) {
                return $b[$a];
            }
        }
        return in_array(' ', $b) ? null : 'draw';
    }

    /** Ход компьютера: сначала выиграть, потом блокировать, потом центр/угол/рандом. */
    public static function aiMove(array $b): int
    {
        // 1. Может выиграть?
        foreach (self::emptyCells($b) as $i) {
            $t = $b; $t[$i] = 'O';
            if (self::checkWinner($t) === 'O') return $i;
        }
        // 2. Нужно блокировать?
        foreach (self::emptyCells($b) as $i) {
            $t = $b; $t[$i] = 'X';
            if (self::checkWinner($t) === 'X') return $i;
        }
        // 3. Центр
        if ($b[4] === ' ') return 4;
        // 4. Углы
        $corners = [0, 2, 6, 8];
        $freeCorners = array_filter($corners, fn($i) => $b[$i] === ' ');
        if ($freeCorners) return $freeCorners[array_rand($freeCorners)];
        // 5. Что осталось
        $free = self::emptyCells($b);
        return $free[array_rand($free)];
    }

    public static function emptyCells(array $b): array
    {
        return array_keys(array_filter($b, fn($c) => $c === ' '));
    }
}