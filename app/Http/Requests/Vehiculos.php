<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class Vehiculos extends FormRequest
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
            'marcas' => ['required'],
            'modelos' => ['required'],
            'estados' => ['required'],
            'placa' => ['required'],
            'kilometraje' => ['required'],
            'valor' => ['required'],
            'foto1' => ['required','max:5000'],
            'foto2' => ['required','max:5000'],
            'foto3' => ['required','max:5000'],
            'foto4' => ['required','max:5000'],
            'caja' => ['required'],
            'version' => ['required'],
            'linea' => ['required'],
            'soat' => ['required'],
            'tecnomecanica' => ['required'],
            'combustible'=>'nullable',
            'cilindraje'=>'nullable',
            'facecolda'=>'nullable',
            'accesorios'=>'nullable',
            'llave'=>'nullable',
            'chasis'=>'nullable',
            'color'=>'nullable',
            'motor'=>'nullable',
            'matricula'=>'nullable',
            'tipo'=>'nullable',
            'servicio'=>'nullable',
            'serie'=>'nullable',
            
        ];
    }
    public function messages()
    {
        return [
            'marca.required' => ['la marca es requerida'],
            'modelo.required' => ['el modelo es requerido'],
            'estado.required' => ['el estado es requerido'],
            'placa.required' => ['la placa es requerida'],
            'kilometraje.required' => ['el kilometraje es requerido'],
            'valor.required' => ['el valor es requerido'],
            'descripcion.required' => ['la descripcion es requerida'],
            'foto1.required' =>['No se envio la imagen 1'],
            'foto2.required' =>['No se envio la imagen 2'],
            'foto3.required' =>['No se envio la imagen 3'],
            'foto4.required' =>['No se envio la imagen 4'],
            'foto1.image'=>['el archivo no corresponde a una imagen validad imagen1'],
            'foto2.image'=>['el archivo no corresponde a una imagen validad imagen2'],
            'foto3.image'=>['el archivo no corresponde a una imagen validad imagen3'],
            'foto4.image'=>['el archivo no corresponde a una imagen validad imagen4'],
            'foto1.mimes'=>['la imagen1 no tiene un formato valido para la carga'],
            'foto2.mimes'=>['la imagen2 no tiene un formato valido para la carga'],
            'foto3.mimes'=>['la imagen3 no tiene un formato valido para la carga'],
            'foto4.mimes'=>['la imagen4 no tiene un formato valido para la carga'],
            'foto1.max'=>['la imagen1 supera eltamaño permitido 5 mp'],
            'foto2.max'=>['la imagen2 supera eltamaño permitido 5 mp'],
            'foto3.max'=>['la imagen3 supera eltamaño permitido 5 mp'],
            'foto4.max'=>['la imagen4 supera eltamaño permitido 5 mp'],
        ];
    }
}
