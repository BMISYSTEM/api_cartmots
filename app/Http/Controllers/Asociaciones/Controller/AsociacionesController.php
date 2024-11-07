<?php

namespace App\Http\Controllers\Asociaciones\Controller;

use App\Http\Controllers\Asociaciones\Implementacion\AsociacionesImplement;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AsociacionesController extends Controller
{
    protected $asociaciones;
    function __construct(AsociacionesImplement $implement)
    {
        $this->asociaciones = $implement;
    }

    function createAsociacion(Request $request): object
    {
        $request = $request->validate(
            [
                'empresa_receptora' => 'required|exists:empresas,id',
                'clientes' => 'required|numeric',
                'vehiculo' => 'required|numeric',
            ],
            [
                'empresa_receptora.required' => 'El campo empresa receptora es obligatorio',
                'empresa_receptora.exists' => 'la empresa receptora no existe',
                'clientes.required' => 'El campo clientes es requerido',
                'clientes.numeric' => 'El campo clientes debe ser 1 o 0 ',
                'vehiculo.required' => 'El campo vehiculo es requerido',
                'vehiculo.numeric' => 'El campo vehiculo debe ser 1 o 0 ',
            ]
        );
        $estatus = $this->asociaciones->createAsociacion(
                                                         $request['empresa_receptora'], 
                                                         $request['vehiculo'], 
                                                         $request['clientes']);
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
    
    function indexsolicitudes():object
    {
        $estatus = $this->asociaciones->indexsolicitudes();
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
    function indexsolicitudesRecibidas():object
    {
        $estatus = $this->asociaciones->indexsolicitudesRecibidas();
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
    function indexasociaciones():object
    {
        $estatus = $this->asociaciones->indexasociaciones();
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
    function cancelarEnvioSolicitud(Request $request):object
    {
        $estatus = $this->asociaciones->cancelarEnvioSolicitud($request->query('id'));
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200); 
    }
    function updateSolicitud(Request $request):object
    {
        $request = $request->validate(
            [
                'id'=>'required|exists:solicitud_asociaciones,id',
                'vehiculo'=> 'required|numeric',
                'clientes'=> 'required|numeric',
            ],
            [
                'id.required' => 'El id es requerido',
                'id.exists' => 'El id no existe en las solicitudes',
                'vehiculo.required' => 'El vehiculo es requerido',
                'vehiculo.numeric' => 'El dato de vehiculo debe ser numerico 1 o 0',
                'clientes.required' => 'El clientes es requerido',
                'clientes.numeric' => 'El dato de clientes debe ser numerico 1 o 0',

            ]
            );
        $estatus = $this->asociaciones->updateSolicitud($request['id'],$request['vehiculo'],$request['clientes']);
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
    function AprobarSolicitud(Request $request)
    {
        $estatus = $this->asociaciones->AprobarSolicitud($request->query('id'));
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
}
