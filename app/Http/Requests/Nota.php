<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Nota extends FormRequest
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
            'comentario' => ['required','max:220'],
            'proximo' => ['required'],
            'hora' => ['required'],
            'estado' => ['required'],
            // 'resultado' => ['required'],
            'cliente' => ['required'],
            'placa'=>['nullable'],
            'valorventa'=>['nullable'],
            'porcentaje'=>['nullable'],
            'placaretoma'=>['nullable'],
            'valorretoma'=>['nullable'],
            'metodo'=>['nullable'],
            'vcredito'=>['nullable'],
            'vcuotaInicial'=>['nullable'],
            'vseparacion'=>['nullable'],
            'vtraspaso'=>['nullable'],
            'asesorios'=>['nullable'],
            'obsequios'=>['nullable'],
            'segundoPrecio'=>['nullable'],
            'entrega'=>['nullable'],
            'vendedor'=>['nullable'],
            'clausulasAdiccionales'=>['nullable'],
        ];
    }
    public function messages()
    {
        return [
            'comentario.max'=>'El comentario digitado supera los caracteres permitidos maximo 220',
            'comentario.required'=>'El nombre es requerido',
            'proximo.required'=>'la fecha del proximo seguimiento es requerida',
            'proximo.required'=>'la hora del proximo seguimiento es requerida',
            'estado.required'=>'El estado es requerido',
            // 'resultado.required'=>'El resultado es requerido',
            'cliente.required'=>'El cliente es requerido',
        ];
    }
}
