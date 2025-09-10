<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHeaderProcessRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'workOrder' => 'required',
            'class' => 'required',
            'process' => 'required',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i',
            'date' => 'required|date', 
            'machine' => 'required',
        ];
        if($this->input("subprocess")) {
            $rules['subprocess'] = 'required';
        }

        return $rules;
    }
}
