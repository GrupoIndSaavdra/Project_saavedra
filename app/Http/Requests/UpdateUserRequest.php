<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesUserAssignment;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    use ValidatesUserAssignment;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->normalizarAsignacion();

        // Las matrículas existentes (incluidas las legacy) se conservan: solo se valida si cambia
        if ($this->has('matricula')) {
            $matricula = strtoupper(str_replace(' ', '', trim((string) $this->input('matricula'))));
            $actual = User::whereKey($this->route('id'))->value('matricula');

            if ($matricula === '' || $matricula === $actual) {
                $this->request->remove('matricula');
            } else {
                $this->merge(['matricula' => $matricula]);
            }
        }
    }

    public function rules(): array
    {
        $userId = (int) $this->route('id');

        return array_merge([
            'matricula' => ['sometimes', 'required', 'string', 'regex:/^\d{2,3}-[A-Za-z0-9]{4,6}$/', 'unique:users,matricula,' . $userId],
            'nombre' => 'required|string|max:255',
            'a_paterno' => 'required|string|max:255',
            'a_materno' => 'required|string|max:255',
            'turno' => 'nullable|string|in:MIXTO,MATUTINO,VESPERTINO,NOCTURNO',
            'contrasena' => 'nullable|string|min:8|max:12',
        ], $this->reglasAsignacion());
    }

    public function withValidator(Validator $validator): void
    {
        $this->validarCoherenciaAsignacion($validator, $this->has('matricula'));
    }

    public function messages(): array
    {
        return array_merge([
            'matricula.unique' => 'La matrícula ya está registrada en otro usuario.',
            'matricula.regex' => 'La matrícula debe tener el formato de prefijo (ej. 11-) + 4 a 6 caracteres del checador.',
            'contrasena.min' => 'La contraseña debe tener entre 8 y 12 caracteres.',
            'contrasena.max' => 'La contraseña debe tener entre 8 y 12 caracteres.',
        ], $this->mensajesAsignacion());
    }
}

