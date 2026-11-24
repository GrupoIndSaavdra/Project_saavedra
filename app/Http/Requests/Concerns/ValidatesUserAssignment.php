<?php

namespace App\Http\Requests\Concerns;

use App\Models\Perfil;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Validation\Validator;

/**
 * Normalización y validación cruzada Planta → Puesto → Perfil (+ prefijo de matrícula).
 * Compartido por CreateUserRequest y UpdateUserRequest.
 */
trait ValidatesUserAssignment
{
    /**
     * Normaliza planta y fuerza puesto en roles reservados (Master).
     */
    protected function normalizarAsignacion(): void
    {
        $planta = mb_strtoupper(trim((string) $this->input('planta')), 'UTF-8');
        $planta = str_starts_with($planta, 'TEC') ? 'TECAMAC' : $planta;

        $perfil = (int) $this->input('perfil');
        $data = ['planta' => $planta, 'perfil' => $perfil ?: null, 'perfil_id' => $perfil ?: null];

        if ($perfil === Perfil::MASTER && empty($this->input('puesto_id'))) {
            $data['puesto_id'] = Puesto::where('id', '=', 1, 'and')->value('id') ?? 1;
        }

        $this->merge($data);
    }

    protected function reglasAsignacion(): array
    {
        return [
            'planta' => 'required|string|in:TECAMAC,CDMX',
            'perfil' => 'required|integer|exists:perfiles,id',
            'perfil_id' => 'nullable|integer',
            'puesto_id' => 'required|integer|exists:puestos,id',
        ];
    }

    /**
     * Validación cruzada de coherencia. Si $validarPrefijo es true, exige que la matrícula
     * empiece con el prefijo calculado a partir del perfil y puesto.
     */
    protected function validarCoherenciaAsignacion(Validator $validator, bool $validarPrefijo): void
    {
        $validator->after(function (Validator $v) use ($validarPrefijo) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $perfil = Perfil::find($this->input('perfil'));
            $puesto = Puesto::find($this->input('puesto_id'));

            if (!$perfil || !$puesto) {
                return;
            }

            if ($validarPrefijo) {
                $esperado = User::prefijoMatricula($perfil, null, $puesto);
                if (!str_starts_with((string) $this->input('matricula'), $esperado)) {
                    $v->errors()->add('matricula', "La matrícula debe iniciar con el prefijo {$esperado} según el perfil y puesto seleccionados.");
                }
            }
        });
    }

    protected function mensajesAsignacion(): array
    {
        return [
            'planta.in' => 'Selecciona una planta válida (TECÁMAC o CDMX).',
            'perfil.exists' => 'Selecciona un tipo de perfil válido.',
            'puesto_id.required' => 'Selecciona el puesto del usuario.',
        ];
    }
}

