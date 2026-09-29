<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Game extends Model
{
    protected $fillable = ['winner', 'mode', 'moves', 'analysis'];
    protected $casts = ['moves' => 'array', 'analysis' => 'array'];

}
