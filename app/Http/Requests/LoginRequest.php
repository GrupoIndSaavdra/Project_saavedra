<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Factory as ValidatioFactory;
use Illuminate\Support\Facades\Hash;

/**
 * @property string $matricula
 * @property string $contrasena
 */
class LoginRequest extends FormRequest
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
            'matricula' => 'required|string|max:50',
            'contrasena' => 'required|string|max:100',
        ];
    }
    public function getCredentials(){
        return [
            'matricula' => $this->get('matricula'),
            'contrasena' => $this->get('contrasena'),
        ];
    }
}
