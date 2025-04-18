<?php

namespace App\Http\Controllers\GeneradorReportes\Controllers;

use App\Http\Controllers\Controller;
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
}