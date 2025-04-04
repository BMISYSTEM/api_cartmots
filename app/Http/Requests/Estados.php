<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Estados extends FormRequest
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
            'nombre' => ['required'],
            'color'=> ['required'],
            'pendiente'=> ['required'],
            'aprobado'=> ['required'],
            'rechazado'=> ['required'],
            'finalizado'=> ['required'],
            'vendido'=> ['required'],
            'chat'=> ['required'],

        ];
    }
    public function messages()
    {
        return [
            'nombre.required' => 'es obligatorio el nombre',
            'nombre.unique' => 'el estado ya esta registrado',
            'color'=> 'es obligatorio el color',
        ];
    }
}
