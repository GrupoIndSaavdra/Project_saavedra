<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property int $pieza_id
 * @property string $n_pieza
 * @property string|null $resultado_pico_llenado
 * @property string|null $resultado_pico_soldadura
 * @property string|null $resultado_conexion_llenado
 * @property string|null $resultado_conexion_soldadura
 * @property string|null $resultado_perfilado_llenado
 * @property string|null $resultado_perfilado_soldadura
 * @property \Illuminate\Http\UploadedFile|null $imagen_pico_soldadura
 * @property \Illuminate\Http\UploadedFile|null $imagen_conexion_soldadura
 * @property \Illuminate\Http\UploadedFile|null $imagen_perfilado_soldadura
 */
class StorePtaResultadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'pieza_id' => ['required', 'exists:piezas,id'],
            'n_pieza' => ['required', 'string'],
            'resultado_pico_llenado' => ['nullable', 'in:Si,No,No Aplica'],
            'resultado_pico_soldadura' => ['nullable', 'in:Si,No,No Aplica'],
            'resultado_conexion_llenado' => ['nullable', 'in:Si,No,No Aplica'],
            'resultado_conexion_soldadura' => ['nullable', 'in:Si,No,No Aplica'],
            'resultado_perfilado_llenado' => ['nullable', 'in:Si,No,No Aplica'],
            'resultado_perfilado_soldadura' => ['nullable', 'in:Si,No,No Aplica'],
            'imagen_pico_soldadura' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,gif,bmp', 'max:10240'],
            'imagen_conexion_soldadura' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,gif,bmp', 'max:10240'],
            'imagen_perfilado_soldadura' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,gif,bmp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'pieza_id.required' => 'Debes seleccionar una pieza.',
            'pieza_id.exists' => 'La pieza seleccionada no existe.',
            'n_pieza.required' => 'El número de pieza es requerido.',
            '*.in' => 'El valor debe ser Si, No o No Aplica.',
            '*.mimes' => 'La imagen debe ser de tipo: jpeg, jpg, png, webp, gif o bmp.',
            '*.max' => 'La imagen no debe superar 10 MB.',
        ];
    }
}
