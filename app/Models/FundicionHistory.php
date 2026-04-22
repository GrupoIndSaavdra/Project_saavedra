<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundicionHistory extends Model
{
    use HasFactory;

    protected $table = 'fundicion_history';

    protected $fillable = [
        'ot',
    ];
}
