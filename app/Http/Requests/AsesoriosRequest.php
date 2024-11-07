<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AsesoriosRequest extends FormRequest
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
            'nombre'=>['required'],
            'marcas'=>['required'],
            'estados'=>['required'],
            'descripcion'=>['required'],
            'valor'=>['required'],
            'foto1' => ['required','max:5000'],
            'foto2' => ['required','max:5000'],
            'foto3' => ['required','max:5000'],

        ];
    }
    public function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'marca.required' => 'La marca es obligatorio',
            'estado.required' => 'El estado es obligatorio',
            'descripcion.required' => 'La descripcion es obligatorio',
            'valor.required' => 'El valor es obligatorio'
        ];
    }
}
