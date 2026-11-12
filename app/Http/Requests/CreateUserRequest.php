<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesUserAssignment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateUserRequest extends FormRequest
{
    use ValidatesUserAssignment;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->normalizarAsignacion();

        // La matrícula puede venir armada o como prefijo + sufijo
        $matricula = trim((string) $this->input('matricula'));
        $prefijo = trim((string) $this->input('matricula_prefijo'));
        $sufijo = trim((string) $this->input('matricula_sufijo'));

        if ($prefijo !== '' && $sufijo !== '') {
            $matricula = $prefijo . $sufijo;
        }

        $this->merge(['matricula' => strtoupper(str_replace(' ', '', $matricula))]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return array_merge([
            // [Prefijo 2 a 3 dígitos]-[Checador 4 a 6] => 7 a 10 caracteres
            'matricula' => ['required', 'string', 'regex:/^\d{2,3}-[A-Za-z0-9]{4,6}$/', 'unique:users,matricula'],
            'nombre' => 'required|string|max:255',
            'a_paterno' => 'required|string|max:255',
            'a_materno' => 'required|string|max:255',
            'contrasena' => 'required|string|min:8|max:12',
            'turno' => 'required|string|in:MIXTO,MATUTINO,VESPERTINO,NOCTURNO',
        ], $this->reglasAsignacion());
    }

    public function withValidator(Validator $validator): void
    {
        $this->validarCoherenciaAsignacion($validator, true);
    }

    public function messages(): array
    {
        return array_merge([
            'required' => 'Completa el campo :attribute.',
            'string' => 'El campo :attribute debe contener texto.',
            'max' => 'El campo :attribute admite hasta :max caracteres.',
            'min' => 'El campo :attribute debe tener al menos :min caracteres.',
            'matricula.unique' => 'La matrícula ya está registrada. Revisa el usuario existente o captura otra matrícula.',
            'matricula.regex' => 'La matrícula debe tener el formato de prefijo (ej. 11-) + 4 a 6 caracteres del checador.',
            'contrasena.min' => 'La contraseña debe tener entre 8 y 12 caracteres.',
            'contrasena.max' => 'La contraseña debe tener entre 8 y 12 caracteres.',
        ], $this->mensajesAsignacion());
    }

    public function attributes(): array
    {
        return [
            'matricula' => 'matrícula',
            'nombre' => 'nombre',
            'a_paterno' => 'apellido paterno',
            'a_materno' => 'apellido materno',
            'contrasena' => 'contraseña',
            'perfil' => 'tipo de perfil',
            'turno' => 'turno',
            'planta' => 'planta',
            'puesto_id' => 'puesto',
        ];
    }
}
