<?php

namespace App\Http\Controllers\Actividades;

use App\Http\Controllers\Actividades\Implement\ActividadesImplement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActividadesController extends Controller
{
    private $actividades;
    function __construct(ActividadesImplement $implement)
    {
        $this->actividades = $implement;
    }

    function createActividad(Request $request): object
    {
        // validacion
        $request = $request->validate(
            [
                'nombre' => 'required'
            ],
            [
                'nombre.required' => 'El nombre es obligatorio'
            ]
        );
        // implementacion
        $estatus = $this->actividades->createActividad($request['nombre']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }

    function updateActividad(Request $request): object
    {
        // validacion
        $request = $request->validate(
            [
                'id' => 'required',
                'nombre' => 'required'
            ],
            [
                'id.required' => 'El id es obligatorio',
                'nombre.required' => 'El nombre es obligatorio'
            ]
        );
        // implementacion
        $estatus = $this->actividades->updateActividad($request['id'], $request['nombre']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }

    function deleteActividad(Request $request): object
    {
        // validacion
        $request = $request->validate(
            [
                'id' => 'required',
            ],
            [
                'id.required' => 'El id es obligatorio',
            ]
        );
        // implementacion
        $estatus = $this->actividades->deleteActividad($request['id']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    function findActividad(Request $request): object
    {
        // validacion
        $request = $request->validate(
            [
                'id' => 'required',
            ],
            [
                'id.required' => 'El id es obligatorio',
            ]
        );
        // implementacion
        $estatus = $this->actividades->findActividad($request['id']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    function allActividad():object
    {
          // implementacion
          $estatus = $this->actividades->allActividad();
          // respuesta
          return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
}
