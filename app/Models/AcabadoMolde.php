<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcabadoMolde extends Model
{
    use HasFactory;
    protected $table = 'acabadomolde';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
