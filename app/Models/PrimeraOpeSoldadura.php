<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrimeraOpeSoldadura extends Model
{
    use HasFactory;
    protected $table = 'primeraopesoldadura';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
