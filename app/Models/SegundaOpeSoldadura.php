<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SegundaOpeSoldadura extends Model
{
    use HasFactory;
    protected $table = 'segundaopesoldadura';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
