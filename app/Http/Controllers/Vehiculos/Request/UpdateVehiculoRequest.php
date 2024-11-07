<?php
namespace App\Http\Controllers\Vehiculos\Request;
class UpdateVehiculoRequest 
{
    static function validate($request):array
    {
        $request = $request->validate(
            [
                'id_vehiculo'=>'required|exists:vehiculos,id',
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
                'proveedor'=>'nullable',
                'precio_proveedor'=>'nullable'
            ],
            [
                'id_vehiculo.required'=>'El vehiculo es requerido',
                'id_vehiculo.exists'=>'El vehiculo no existe',
                'marcas.required' => 'El campo marcas es obligatorio',
                'modelos.required' => 'El campo modelos es obligatorio',
                'estados.required' => 'El campo estados es obligatorio',
                'marcas.exists' => 'El campo marcas no existe en la db ',
                'modelos.exists' => 'El campo modelos no existe en la db ',
                'estados.exists' => 'El campo estados no existe en la db ',
                'placa.required' => 'El campo placa es obligatorio',
                'kilometraje.required' => 'El campo kilometraje es obligatorio',
                'valor.required' => 'El campo valor es obligatorio',
                'disponibilidad.required' => 'El campo disponibilidad es obligatorio',
                'caja.required' => 'El campo caja es obligatorio',
                'version.required' => 'El campo version es obligatorio',
                'linea.required' => 'El campo linea es obligatorio',
                'soat.required' => 'El campo soat es obligatorio',
                'tecnicomecanica.required' => 'El campo tecnicomecanica es obligatorio',
            ]
        );

        return $request;
    }
}