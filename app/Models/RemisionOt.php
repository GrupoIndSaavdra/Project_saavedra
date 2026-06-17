<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemisionOt extends Model
{
    protected $table = 'remisiones_ot';

    protected $fillable = [
        'id_ot',
        'id_clase',
        'filename',
        'path',
        'descripcion',
        'uploaded_by',
        'visible',
    ];

    public function clase()
    {
        return $this->belongsTo(Clase::class, 'id_clase');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'matricula');
    }
}
