<?php

namespace App\Http\Controllers;

use App\Http\Requests\Estados;
use App\Models\empresa;
use App\Models\estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EstadoController extends Controller
{
    public function create(Estados $request)
    {
        $estado = $request->validated();
        $empresas = Auth::user()->empresas;
        $creado = estado::create([
            'estado' => $estado['nombre'],
            'color'  => $estado['color'],
            'pendiente'  => $estado['pendiente'],
            'aprobado'  => $estado['aprobado'],
            'rechazado'  => $estado['rechazado'],
            'vendido'  => $estado['vendido'],
            'finalizado'  => $estado['finalizado'],
            'chat'  => $estado['chat'],
            'empresas'  => $empresas,
        ]);
        return "se creo correctamente";
    }
    public function update(Request $request)
    { 
            $mensaje = 'Faltan campos';
            $empresas = Auth::user()->empresas;
            $id = $request['id'];
            $estadoupdate = estado::find($id);
            $estadoupdate->estado = $request['nombre'];
            $estadoupdate->color  =$request['color'];
            $estadoupdate->pendiente = $request['pendiente'];
            $estadoupdate->aprobado  = $request['aprobado'];
            $estadoupdate->rechazado = $request['rechazado'];
            $estadoupdate->finalizado = $request['finalizado'];
            $estadoupdate->vendido = $request['vendido'];
            $estadoupdate->chat = $request['chat'];
            $estadoupdate->empresas  = $empresas;
            $estadoupdate->save();
            $mensaje = 'Se actualizo de forma correcta';
        return $mensaje;
    }
    public function index()
    {
        $empresa = Auth::user()->empresas;
        $query = DB::select('select * from estados where empresas = '. $empresa);
        return response()->json(['estados'=>$query]);
    }
}
