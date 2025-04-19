<?php

namespace App\Http\Controllers\GeneradorReportes\Controllers;

use App\Http\Controllers\Controller;
use App\Models\reporte_fuente_dato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeneradorReportes extends Controller
{
    /* consulta sql libre  */
    public function ConsultaSQL(Request $request)
    {
        $consultaSql = $request['sql'];
        $consulta = DB::select($consultaSql);
        return response()->json($consulta);
    }

    /* guardar fuente de datos  */

    public function saveFuenteData(Request $request){
        $empresas = Auth::user()->empresas;
        try {
            //code...
            reporte_fuente_dato::create(
                [
                    "nombre"=>$request['nombre'],
                    "consulta"=>"no definida",
                    "descripcion"=>$request['descripcion'],
                    "empresas"=>$empresas
                ]
            );
            return response()->json(['succes'=>'La fuente se creo de forma correcta']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>'Se genero un error al almacenar la fuente de datos error= '.$th]);
        }
    }

    /* consulta todas las fuentes de datos  */
    public function fuenteDataAll(){
        $empresas = Auth::user()->empresas;
        $data = reporte_fuente_dato::where('empresas',$empresas)->get();
        return response()->json($data);
    }
    /* guarda la consulta de una fuente de datos  */

    public function saveConsultaFuenteData(Request $request)
    {
        $validate = $request->validate(
            [
                'id'=>'required',
                'consulta'=>'required'
            ],
            [
                'id.required'=>'es obligatorio el id de la fuente de datos',
                'consulta.required'=>'La consulta es obligatoria'
            ]
        );
        try {
            //code...
            $fuente = reporte_fuente_dato::find($validate['id']);
            $fuente->consulta = $validate['consulta'];
            $fuente->save();
            return response()->json(['succes'=>'La conculta fue almacenada con exito']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>'Error generado al momento de almacenar la consulta error= '.$th]);
        }
    }
    /* Ejecutar una consulta de una fuente de datos  */
    public function ejecutFuenteData(Request $request)
    {
        $fuente = reporte_fuente_dato::find($request['id']);
        try {
            //code...
            $resultSql = DB::select($fuente->consulta);
            return response()->json($resultSql);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>'se genero un error al momento de cargar la consulta error ='.$th]);
        }
    }

}