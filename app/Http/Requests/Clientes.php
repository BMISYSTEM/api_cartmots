<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Clientes extends FormRequest
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
            'telefono'=>['required','numeric'],
            'email'=>['nullable'],
            'nombre'=>['nullable'],
            'apellido'=>['nullable'],
            'cedula'=>['nullable'],
            'data'=>['nullable'],
            'origen'=>['required','numeric']
            //
        ];
    }
    public function messages()
    {
        return [
            'telefono.required'=>'debe ingresar un telefono',
            'telefono.numeric'=>'debe ingresar un telefono valido',
        ];
    }
}
