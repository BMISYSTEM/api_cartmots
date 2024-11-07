<?php
namespace App\Http\Controllers\Motivos;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Motivos\Implement\MotivosImplement;
use Illuminate\Http\Request;

class MotivosController extends Controller
{
    private $motivo;
    function __construct(MotivosImplement $implement)
    {
        $this->motivo = $implement;
    }

    function createMotivo(Request $request ):object
    {
        // validacion
        $request = $request->validate(
            [
                'nombre'=>'required'
            ],
            [
                'nombre.required'=>'El nombre es obligatorio'
            ]
            );
        // implementacion
        $estatus = $this->motivo->createMotivo($request['nombre']);
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
    function updateMotivo(Request $request):object
    {
        // validacion
        $request = $request->validate(
            [
                'id'=>'required',
                'nombre'=>'required'
            ],
            [
                'id.required'=>'El id es obligatorio',
                'nombre.required'=>'El nombre es obligatorio'
            ]
            );
        // implementacion
        $estatus = $this->motivo->updateMotivo($request['id'],$request['nombre']);
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }

    function deleteMotivo(Request $request):object
    {
        $request = $request->validate(
            [
                'id'=>'required',
            ],
            [
                'id.required'=>'El id es obligatorio',
            ]
            );
        // implementacion
        $estatus = $this->motivo->deleteMotivo($request['id']);
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }

    function findMotivo(Request $request):object
    {
        $request = $request->validate(
            [
                'id'=>'required',
            ],
            [
                'id.required'=>'El id es obligatorio',
            ]
            );
        // implementacion
        $estatus = $this->motivo->findMotivo($request['id']);
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
    function allMotivo():object
    {
        $estatus = $this->motivo->allMotivo();
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
}