<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DibujoOtHistory extends Model
{
    protected $table = 'dibujos_ot_history';

    protected $fillable = [
        'ot',
        'clase',
    ];
}
