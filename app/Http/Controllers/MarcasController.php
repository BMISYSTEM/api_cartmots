<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marca;
use App\Models\marcas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarcasController extends Controller
{
    public function index()
    {
        $empresa = Auth::user()->empresas;
        $query = DB::select('select * from marcas where empresas = '. $empresa);
        return response()->json(['marcas'=>$query]);
    }

    public function create(Marca $request){
        $nombre = $request->validated();
        $empresa = Auth::user()->empresas;
        $marcas = marcas::create([
            'nombre'=>$nombre['nombre'],
            'empresas'=>$empresa
        ]);
        return ['mensaje'=>'creado correctamente'];
    }

    public function update(Request $request)
    {
        $id = $request['id'];
        $marca = marcas::find($id);
        $marca->nombre = $request['marca'];
        $marca->save();
        return response()->json('se actualizo de forma correcta');
    }
}
