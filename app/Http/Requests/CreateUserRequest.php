<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'matricula' => 'required|string|min:4|max:7|unique:users,matricula',
            'nombre' => 'required|string|max:255',
            'a_paterno' => 'required|string|max:255',
            'a_materno' => 'required|string|max:255',
            'contrasena' => 'required|string|min:8|max:12',
            'perfil' => 'required|integer|in:1,2,3,4,5,6,8',
            'turno' => 'required|string|max:50',
            'planta' => 'required|string|max:50',
            'area' => 'required|string|max:100',
            'puesto' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Completa el campo :attribute.',
            'string' => 'El campo :attribute debe contener texto.',
            'max' => 'El campo :attribute admite hasta :max caracteres.',
            'matricula.unique' => 'La matrícula ya está registrada. Revisa el usuario existente o captura otra matrícula.',
            'matricula.min' => 'La matrícula debe tener entre 4 y 7 caracteres.',
            'matricula.max' => 'La matrícula debe tener entre 4 y 7 caracteres.',
            'contrasena.min' => 'La contraseña debe tener entre 8 y 12 caracteres.',
            'contrasena.max' => 'La contraseña debe tener entre 8 y 12 caracteres.',
            'perfil.integer' => 'Selecciona un tipo de perfil válido.',
            'perfil.in' => 'Selecciona un tipo de perfil válido.',
            'puesto.required' => 'Selecciona un área y después el puesto del usuario.',
        ];
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
            'area' => 'área',
            'puesto' => 'puesto',
        ];
    }
}
