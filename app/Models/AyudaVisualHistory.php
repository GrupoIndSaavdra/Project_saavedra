<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AyudaVisualHistory extends Model
{
    use HasFactory;

    protected $table = 'ayudas_visuales_history';

    protected $fillable = [
        'proceso',
        'clase',
    ];
}
