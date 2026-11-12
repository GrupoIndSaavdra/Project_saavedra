<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'matricula',
        'nombre',
        'a_paterno',
        'a_materno',
        'contrasena',
        'perfil',
        'perfil_id',
        'turno',
        'planta',
        'puesto_id',
        'prod_status',
        'prod_start_at',
        'prod_locked_type',
        'prod_standard_min',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'contrasena',
        'remember_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'matricula_verified_at' => 'datetime',
        'contrasena' => 'hashed',
    ];

    public function setPasswordAttribute($value){
        $this->attributes['contrasena'] = bcrypt($value);
    }

    public function getNameAttribute()
    {
        return $this->nombre . ' ' . $this->a_paterno . ' ' . $this->a_materno;
    }

    // Accessors para retrocompatibilidad con vistas existentes:
    public function getAreaAttribute(): string
    {
        return '';
    }

    public function getPuestoAttribute(): string
    {
        return $this->puestoInfo->nombre ?? '';
    }

    /**
     * Etiqueta oficial de planta para mostrar en vistas (el valor en BD es TECAMAC / CDMX).
     */
    public function getPlantaLabelAttribute(): string
    {
        return match ($this->planta) {
            'TECAMAC' => 'TECÁMAC',
            null, '' => '',
            default => (string) $this->planta,
        };
    }

    /**
     * Perfiles (roles) válidos para una combinación Área + Nivel de puesto.
     * Debe mantenerse sincronizado con resources/js/users_views/userAssignment.js
     */
    public static function perfilesPermitidos(int $areaId, int $nivel): array
    {
        return match ($areaId) {
            1 => [Perfil::MASTER],
            2 => [6],
            3 => [5],
            4 => [4],
            5 => [8],
            8 => [7],
            10 => [2],
            11 => [Perfil::SISTEMAS],
            6, 7, 9 => $nivel <= 2 ? [Perfil::SUPERVISOR] : [Perfil::OPERADOR],
            default => [],
        };
    }

    /**
     * Prefijo de matrícula de 2 dígitos + guion (Perfil + Nivel de Puesto).
     * Master: 11- | Sistemas: 01- | Resto: {codigo_perfil}{codigo_nivel}-
     */
    public static function prefijoMatricula(Perfil $perfil, mixed $area = null, ?Puesto $puesto = null): string
    {
        if ((int) $perfil->id === Perfil::MASTER) {
            return '11-';
        }

        $cPerfil = (string) ($perfil->codigo_perfil ?? $perfil->id);
        $cNivel  = (string) ($puesto->codigo_nivel ?? '1');

        return $cPerfil . $cNivel . '-';
    }

    public function perfilInfo()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id');
    }

    public function puestoInfo()
    {
        return $this->belongsTo(Puesto::class, 'puesto_id');
    }
}
