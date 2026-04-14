<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DibujoFileLog extends Model
{
    protected $table = 'dibujos_file_log';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'ruta',
        'archivo',
    ];

    /**
     * Relación con el usuario que realizó la acción.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
