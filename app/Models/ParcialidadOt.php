<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParcialidadOt extends Model
{
    protected $table = 'parcialidades_ot';

    protected $fillable = [
        'id_ot',
        'id_clase',
        'id_remision',
        'cantidad',
        'descripcion',
        'fecha_recepcion',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
    ];

    public function clase()
    {
        return $this->belongsTo(Clase::class, 'id_clase');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'matricula');
    }

    public function remision()
    {
        return $this->belongsTo(RemisionOt::class, 'id_remision');
    }
}
