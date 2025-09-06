<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rectificado extends Model
{
    use HasFactory;
    protected $table = 'rectificado';
    protected $fillable = [
        'cumple',
        'observaciones'
    ];
}
