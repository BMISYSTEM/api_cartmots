<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GeneradorReportes extends Controller
{
    public function ConsultaSQL(Request $request)
    {
        $consulta = DB::select($request['sql']);
        return response()->json($consulta);
    }
}