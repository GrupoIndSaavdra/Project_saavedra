<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualHistory extends Model
{
    use HasFactory;

    protected $table = 'manuales_history';

    protected $fillable = [
        'proceso',
    ];
}
