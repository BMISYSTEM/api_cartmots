<?php

namespace App\Http\Controllers;

use App\Http\Requests\AsesoriosRequest;
use App\Models\asesorio;
use App\Models\empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AsesorioController extends Controller
{
    public function create(AsesoriosRequest $request)
    {
        $asesorios = $request->validated();
        $empresa = Auth::user()->empresas;
        $nomempresa = empresa::find($empresa);
        // capturar y mover archivos
        $foto1 = $request ->file('foto1')->store('public/'.$nomempresa['nombre'].'/asesorios');
        $foto1url = Storage::url($foto1);
        $foto2 = $request ->file('foto2')->store('public/'.$nomempresa['nombre'].'/asesorios');
        $foto2url = Storage::url($foto2);
        $foto3 = $request ->file('foto3')->store('public/'.$nomempresa['nombre'].'/asesorios');
        $foto3url = Storage::url($foto3);
        $asesorio = asesorio::create(
            [
            'nombre'=> $asesorios['nombre'],
            'marcas'=> $asesorios['marcas'],
            'estados'=> $asesorios['estados'],
            'descripcion'=> $asesorios['descripcion'],
            'valor'=> $asesorios['valor'],
            'foto1'=>$foto1url,
            'foto2'=>$foto2url,
            'foto3'=>$foto3url,
            'empresas' =>$empresa
            ]
        );
        return 'se creo de forma correcta';
    }
    public function index()
    {

        $vista= DB::select('select a.id,a.nombre,a.descripcion,a.valor,a.foto1,a.foto2,a.foto3,m.nombre as marca ,m.id as marca_id,e.estado,e.id as estado_id from asesorios a
        inner join marcas m on m.id = a.marcas
        inner join estados e on e.id = a.estados');
        return response()->json($vista);
    }
}
