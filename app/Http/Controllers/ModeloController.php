<?php

namespace App\Http\Controllers;

use App\Http\Requests\Modelos;
use App\Models\modelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModeloController extends Controller
{
    public function create(Modelos $request)
    {
        $year = $request->validated();
        $empresa = Auth::user()->empresas;
        $create=modelo::create([
            'year'=>$year['year'],
            'empresas'=>$empresa
        ]);
        return $create;
    }
    public function index(){
        $empresa = Auth::user()->empresas;
        $query = DB::select('select * from modelos where empresas = '. $empresa);
        return response()->json(['modelos'=>$query]);
    }
}
