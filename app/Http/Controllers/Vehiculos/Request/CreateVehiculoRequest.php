<?php

namespace App\Http\Controllers\Vehiculos\Request;

class CreateVehiculoRequest
{
 
     static function validate($request):array
    {
        $request = $request->validate(
            [
                'marcas' => 'required|exists:marcas,id',
                'modelos' => 'required|exists:modelos,id',
                'estados' => 'required|exists:estados,id',
                'placa' => 'required',
                'kilometraje' => 'required',
                'valor' => 'required',
                'disponibilidad' => 'required',
                'caja' => 'required',
                'version' => 'required',
                'linea' => 'required',
                'soat' => 'required',
                'tecnicomecanica' => 'required',
                'foto1' => 'required|max:5000|file|mimes:png,jpg',
                'foto2' => 'required|max:5000|file|mimes:png,jpg',
                'foto3' => 'required|max:5000|file|mimes:png,jpg',
                'foto4' => 'required|max:5000|file|mimes:png,jpg',
                'peritaje' => 'nullable',
                'proveedor'=>'nullable',
                'precio_proveedor'=>'nullable',
                'combustible'=>'nullable',
                'cilindraje'=>'nullable',
                'facecolda'=>'nullable',
                'accesorios'=>'nullable',
                'llave'=>'nullable',
            ],
            [
                'marcas.required' => 'El campo marcas es obligatorio',
                'modelos.required' => 'El campo modelos es obligatorio',
                'marcas.exists' => 'El campo marcas no existe en la db ',
                'modelos.exists' => 'El campo modelos no existe en la db ',
                'estados.exists' => 'El campo estados no existe en la db ',
                'placa.required' => 'El campo placa es obligatorio',
                'placa.unique' => 'La placa digitada ya existe',
                'kilometraje.required' => 'El campo kilometraje es obligatorio',
                'valor.required' => 'El campo valor es obligatorio',
                'disponibilidad.required' => 'El campo disponibilidad es obligatorio',
                'caja.required' => 'El campo caja es obligatorio',
                'version.required' => 'El campo version es obligatorio',
                'linea.required' => 'El campo linea es obligatorio',
                'soat.required' => 'El campo soat es obligatorio',
                'tecnicomecanica.required' => 'El campo tecnicomecanica es obligatorio',
                'foto1.required' => 'El campo foto1 es obligatorio',
                'foto2.required' => 'El campo foto2 es obligatorio',
                'foto3.required' => 'El campo foto3 es obligatorio',
                'foto4.required' => 'El campo foto4 es obligatorio',
                'foto1.mimes' => 'El campo foto1 no es png o jpg',
                'foto2.mimes' => 'El campo foto2 no es png o jpg',
                'foto3.mimes' => 'El campo foto3 no es png o jpg',
                'foto4.mimes' => 'El campo foto4 no es png o jpg',
            ]
        );

        return $request;
    }
}