<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GameController::class, 'index'])->name('game');
Route::post('/move', [GameController::class, 'move'])->name('move');
Route::post('/reset', [GameController::class, 'reset'])->name('reset');
Route::get('/mode/{mode}', [GameController::class, 'mode'])->name('mode');
Route::get('/history', [GameController::class, 'history'])->name('history');
Route::get('/history/{game}', [GameController::class, 'replay'])->name('replay');
Route::get('/stats', [GameController::class, 'stats'])->name('stats');