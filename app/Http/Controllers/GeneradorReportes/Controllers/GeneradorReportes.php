<?php

namespace App\Http\Controllers\GeneradorReportes\Controllers;

use App\Http\Controllers\Controller;
use App\Models\reporte;
use App\Models\reporte_fuente_dato;
use App\Models\reportes_config;
use App\Models\reportes_for_fuente;
use App\Models\seccione;
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

    public function findFuenteDatos(Request $request)
    {
        try {
            $reporte = $request->query('id');
            $relacion = reportes_for_fuente::where('reportes',$reporte)->get();
            $fuenteData = reporte_fuente_dato::find($relacion->fuente);
            return response()->json($fuenteData);
        } catch (\Throwable $th) {
            return response()->json(['error'=>'Error generado al momento de consultar la fuente de datos  error= '.$th],500);
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


    /* rerporte layout  */
    public function newSeccion(Request $request) 
    {
        try {
            //code...
            $empresas = Auth::user()->empresas;
            $seccion = seccione::create(
                [
                    'nombre'=>$request['nombre'],
                    'empresas'=>$empresas
                ]
            );
            return response()->json(['succes'=>'Se creo la seccion correctamente'],200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>'Error generado en el servidor errro = '.$th],500);
        }
    }
    public function seccionAll(){
        $empresas = Auth::user()->empresas;
        $secciones = seccione::where('empresas',$empresas)->get();
        return response()->json($secciones);

    }
    public function newReporte(Request $request) 
    {
        try {
            //code...
            $empresas = Auth::user()->empresas;
            $seccion = reporte::create(
                [
                    'nombre'=>$request['nombre'],
                    'secciones'=>$request['seccion'],
                    'empresas'=>$empresas
                ]
            );
            return response()->json(['succes'=>'Se creo el reporte correctamente'],200);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>'Error generado en el servidor errro = '.$th],500);
        }
    }
    public function reportesAll(){
        $empresas = Auth::user()->empresas;
        $reporte = reporte::where('empresas',$empresas)->get();
        return response()->json($reporte);

    }
    /* consulta la informacion de un reporte  */
    function findRelacionReporteFuente(Request $request)
    {   
        $reporte = $request->query('id');
        $fuenteSeleect = reportes_for_fuente::where('reportes',$reporte)->get();
        return response()->json($fuenteSeleect);

    }

    /* creacion de relacion  */
    function createRelacionreporteFuenteDatos(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $relacion= reportes_for_fuente::create(
            [
                'empresas'=>$empresa,
                'fuente'=>$request['fuente'],
                'reportes'=>$request['reporte']
            ]
        );
        $fuente = reporte_fuente_dato::find($request['fuente']);
        $vista = DB::select($fuente->consulta);
        $columnas = [];
        if (!empty($vista)) {
            $columnas = array_keys((array) $vista[0]);
        }
        $pocicion = 1;
        foreach($columnas as $columna)
        {
            reportes_config::create(
                [
                    'campo'=>$columna,
                    'seleccion'=>0,
                    'titulo'=>'',
                    'color'=>'',
                    'filtro'=>'',
                    'posicion'=>$pocicion,
                    'total'=>0,
                    'reportes'=>$request['reporte'],
                    'empresas'=>$empresa
                ]
            );
        }
        return response()->json(['succes'=>'Fuente relacionado con el reporte de forma correcta, sus campos fueron creados correctamente']);
    }


    function camposReporteAll(Request $request){
        $reporte = $request->query('id');
        $campos_reporte = reportes_config::where('reportes',$reporte)->get();
        return response()->json($campos_reporte);

    }


    /* editar campos uno a uno  */
    function editCampos(Request $request)
    {
        try {
            
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    function saveConfigCampos(Request $request){

        try {
            $datos = $request->all();
            foreach ($datos as $fila) {
                if (isset($fila['id'])) {
                    // Actualiza todos los campos excepto el ID
                    reportes_config::where('id', $fila['id'])->update(
                        collect($fila)->except('id', 'created_at', 'updated_at')->toArray()
                    );
                }
            }
            return response()->json(['succes'=>'Se actualizaron todos los campos modificados']);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>'Se genero un error al momento de actualizar los campos '.$th],500);
        }
    }
}