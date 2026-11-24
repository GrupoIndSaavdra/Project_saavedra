<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldaduraPTA extends Model
{
    use HasFactory;
    protected $table = 'soldadurapta';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
