<?php

namespace App\Http\Requests\Concerns;

use App\Models\Area;
use App\Models\Perfil;
use App\Models\Puesto;
use App\Models\User;
use Illuminate\Validation\Validator;

/**
 * Normalización y validación cruzada Planta → Área → Puesto → Perfil (+ prefijo de matrícula).
 * Compartido por CreateUserRequest y UpdateUserRequest.
 */
trait ValidatesUserAssignment
{
    /**
     * Normaliza planta y fuerza área/puesto en roles reservados (Master / Sistemas).
     */
    protected function normalizarAsignacion(): void
    {
        $planta = mb_strtoupper(trim((string) $this->input('planta')), 'UTF-8');
        $planta = str_starts_with($planta, 'TEC') ? 'TECAMAC' : $planta;

        $perfil = (int) $this->input('perfil');
        $data = ['planta' => $planta, 'perfil' => $perfil ?: null, 'perfil_id' => $perfil ?: null];

        if ($perfil === Perfil::MASTER) {
            $data['area_id'] = Area::DIRECCION_GENERAL;
            $data['puesto_id'] = Puesto::where('area_id', '=', Area::DIRECCION_GENERAL, 'and')->orderBy('codigo_nivel')->value('id');
        } elseif ($perfil === Perfil::SISTEMAS) {
            $data['area_id'] = Area::SISTEMAS;
        }

        // Si no se proporcionó area_id explícito pero sí un puesto_id, se deduce automáticamente del puesto
        $puestoId = (int) ($this->input('puesto_id') ?? ($data['puesto_id'] ?? 0));
        if (empty($data['area_id']) && $puestoId > 0) {
            $areaDelPuesto = Puesto::where('id', '=', $puestoId, 'and')->value('area_id');
            if ($areaDelPuesto) {
                $data['area_id'] = (int) $areaDelPuesto;
            }
        }

        $this->merge($data);
    }

    protected function reglasAsignacion(): array
    {
        return [
            'planta' => 'required|string|in:TECAMAC,CDMX',
            'perfil' => 'required|integer|exists:perfiles,id',
            'perfil_id' => 'nullable|integer',
            'area_id' => 'required|integer|exists:areas,id',
            'puesto_id' => 'required|integer|exists:puestos,id',
        ];
    }

    /**
     * Validación cruzada de coherencia. Si $validarPrefijo es true, exige que la matrícula
     * empiece con el prefijo calculado a partir del perfil, área y puesto.
     */
    protected function validarCoherenciaAsignacion(Validator $validator, bool $validarPrefijo): void
    {
        $validator->after(function (Validator $v) use ($validarPrefijo) {
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $perfil = Perfil::find($this->input('perfil'));
            $area = Area::find($this->input('area_id'));
            $puesto = Puesto::find($this->input('puesto_id'));

            if (!$perfil || !$area || !$puesto) {
                return;
            }

            if ((int) $puesto->area_id !== (int) $area->id) {
                $v->errors()->add('puesto_id', 'El puesto seleccionado no pertenece al área elegida.');
                return;
            }

            if (!$area->perteneceAPlanta($this->input('planta'))) {
                $v->errors()->add('area_id', "El área {$area->nombre} no opera en la planta seleccionada.");
                return;
            }

            if (!in_array((int) $perfil->id, User::perfilesPermitidos((int) $area->id, (int) $puesto->codigo_nivel), true)) {
                $v->errors()->add('perfil', "El perfil {$perfil->nombre} no corresponde al área {$area->nombre} / puesto {$puesto->nombre}.");
                return;
            }

            if ($validarPrefijo) {
                $esperado = User::prefijoMatricula($perfil, $area, $puesto);
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
            'area_id.required' => 'Selecciona el área del usuario.',
            'puesto_id.required' => 'Selecciona un área y después el puesto del usuario.',
        ];
    }
}

