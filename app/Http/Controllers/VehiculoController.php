<?php

namespace App\Http\Controllers;

use App\Http\Requests\Vehiculos;
use App\Http\Resources\VehiculosResource;
use App\Models\empresa;
use App\Models\vehiculo;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Exists;
use Mockery\Undefined;
use UnderflowException;

class VehiculoController extends Controller
{
    public function create(Vehiculos $request)
    {
        $vehiculos = $request->validated();
        $peritaje ='';
        $empresa = Auth::user()->empresas;
        $nomempresa = empresa::find($empresa);
        // // cambiar el modelo por las carpetas de cada uno 
        // // capturar todos las imagenes de los vehiculos 
        $foto1 = $request ->file('foto1')->store('public/'.$nomempresa['nombre'].'/vehiculos');
        $foto1url = Storage::url($foto1);
        $foto2 = $request->file('foto2')->store('public/'.$nomempresa['nombre'].'/vehiculos');
        $foto2url = Storage::url($foto2);
        $foto3 = $request->file('foto3')->store('public/'.$nomempresa['nombre'].'/vehiculos');
        $foto3url = Storage::url($foto3);
        $foto4 = $request->file('foto4')->store('public/'.$nomempresa['nombre'].'/vehiculos');
        $foto4url = Storage::url($foto4);
        // insertamos los datos en la base de datos 
        $create = vehiculo::create(
            [
            'placa' => $vehiculos['placa'],
            'kilometraje' => $vehiculos['kilometraje'],
            'foto1' => $foto1url,
            'foto2' => $foto2url,
            'foto3' => $foto3url,
            'foto4' => $foto4url,
            'marcas' => $vehiculos['marcas'],
            'modelos' => $vehiculos['modelos'],
            'estados' => $vehiculos['estados'],
            'valor' => $vehiculos['valor'],
            'peritaje' => $peritaje,
            'empresas'=> $empresa,
            'caja' => $vehiculos['caja'],
            'version' => $vehiculos['version'],
            'linea' => $vehiculos['linea'],
            'soat' => $vehiculos['soat'],
            'tecnomecanica' => $vehiculos['tecnomecanica'],
            ]
        );
        return ['200'=>'creado con exito'];
    }
    public function update(Request $request)
    {
        $update = vehiculo::find($request['id']);
        $update->marcas = $request['marca'];
        $update->modelos = $request['modelo'];
        $update->estados = $request['estado'];
        $update->placa = $request['placa'];
        $update->kilometraje = $request['kilometraje'];
        $update->valor = $request['valor'];
        $update->save();
        return response()->json('actualizado con exito');
    }
    
    public function index()
    {
        $empresa = Auth::user()->empresas;
        return new VehiculosResource(vehiculo::where('empresas',$empresa)->with('marcas')->with('modelos')->with('estados')->get());
    }

    public function updateimg1(Request $request)
    {   
        $urldelete = str_replace('storage','public',$request['oldimg']);
        $empresa = Auth::user()->empresas;
        $nomempresa = empresa::find($empresa);
        Storage::delete($urldelete);
        $newimage = $request->file('foto1')->store('public/'.$nomempresa['nombre'].'/vehiculos');
        $urlimg = Storage::url($newimage);
        $vehiculo = vehiculo::find($request['id']);
        $vehiculo->foto1 = $urlimg;
        $vehiculo->save();
        return response()->json('actualizado con exito');
    }
    public function updateimg2(Request $request)
    {   
        $urldelete = str_replace('storage','public',$request['oldimg']);
        $empresa = Auth::user()->empresas;
        $nomempresa = empresa::find($empresa);
        Storage::delete($urldelete);
        $newimage = $request->file('foto2')->store('public/'.$nomempresa['nombre'].'/vehiculos');
        $urlimg = Storage::url($newimage);
        $vehiculo = vehiculo::find($request['id']);
        $vehiculo->foto2 = $urlimg;
        $vehiculo->save();
        return response()->json('actualizado con exito');
    }
    public function updateimg3(Request $request)
    {   
        $urldelete = str_replace('storage','public',$request['oldimg']);
        $empresa = Auth::user()->empresas;
        $nomempresa = empresa::find($empresa);
        Storage::delete($urldelete);
        $newimage = $request->file('foto3')->store('public/'.$nomempresa['nombre'].'/vehiculos');
        $urlimg = Storage::url($newimage);
        $vehiculo = vehiculo::find($request['id']);
        $vehiculo->foto3 = $urlimg;
        $vehiculo->save();
        return response()->json('actualizado con exito');
    }
    public function updateimg4(Request $request)
    {   
        $urldelete = str_replace('storage','public',$request['oldimg']);
        $empresa = Auth::user()->empresas;
        $nomempresa = empresa::find($empresa);
        Storage::delete($urldelete);
        $newimage = $request->file('foto4')->store('public/'.$nomempresa['nombre'].'/vehiculos');
        $urlimg = Storage::url($newimage);
        $vehiculo = vehiculo::find($request['id']);
        $vehiculo->foto4 = $urlimg;
        $vehiculo->save();
        return response()->json('actualizado con exito');
    }


}
