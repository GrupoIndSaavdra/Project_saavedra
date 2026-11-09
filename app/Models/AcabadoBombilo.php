<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcabadoBombilo extends Model
{
    use HasFactory;
    protected $table = 'acabadobombillo';
    protected $fillable = [
        'id',
        'id_ot'
    ];
}
