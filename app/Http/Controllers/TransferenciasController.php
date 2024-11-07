<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use App\Models\solicitud_asociacione;
use App\Models\transferencias;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransferenciasController extends Controller
{
    // creacion de un nuevo lead de transferencia
    public function create(Request $request)
    {
        // validacion del request 
        $request->validate(
            [
            'id_socio' => 'required|exists:solicitud_asociaciones,id',
            'id_cliente' => 'required|integer',
            'comentario' => 'required',
            ],
            [
            'id_cliente.required'=>'El id del cliente no se envio',
            'comentario.required'=>'El comentario es obligatorio'
            ]
        );
        $sociedad = solicitud_asociacione::find($request['id_socio']);
        $empresa = Auth::user()->empresas;
        $empresaIntercompani = '';
        // se verifica a que empresa se va a enviar 
        if($sociedad->empresa_solicitante === $empresa and $sociedad->empresa_receptora <> $empresa )
        {
            $empresaIntercompani = $sociedad->empresa_receptora;
        }else if ($sociedad->empresa_receptora === $empresa and $sociedad->empresa_solicitante <> $empresa)
        {
            $empresaIntercompani = $sociedad->empresa_solicitante;
        }
        $usuario = Auth::user()->id;
        // creamos el cliente 
        // la empresa de llegada esta quemada, corregir para que quede para cualquier empresa que se seleccione
        $trans = transferencias::create([
            'empresa_de' =>$empresa,
            'empresa_para' =>$empresaIntercompani,
            'clientes'=>$request['id_cliente'],
            'usuario'=>$usuario,
            'comentario'=>$request['comentario']
        ]);
        // update a clieente transferido se le coloca el campo en 1 lo que evita mostrarlo en el seguimiento 
        $cliente = cliente::find($request['id_cliente']);
        $cliente->transferido = 1;
        $cliente->save();
        return response()->json(['confirmacion'=>'transferencia exitosa']);
    }
    // consulta los enviados
    public function index()
    {
        $empresa = Auth::user()->empresas;
        $usuario = Auth::user()->id;
        $rol = Auth::user()->rol;
        // validamos si es super usuario
        if($rol == 1)
        {
            $enviados = DB::select('
            SELECT 
                c.nombre, 
                c.email, 
                c.cedula,
                c.telefono,
                epara.nombre as para, 
                e.estado,
                t.created_at,
                CONCAT(u.name," ",u.apellido) usuario
            from transferencias t
                inner join clientes c on t.clientes = c.id
                inner join empresas ede on t.empresa_de = ede.id
                inner join empresas epara on t.empresa_para = epara.id
                inner join users u on t.usuario = u.id
                inner join estados e on c.estados = e.id
            where ede.id = '. $empresa .' 
            ');
        }else{

            $enviados = DB::select('
            SELECT 
            c.nombre, 
            c.email, 
            c.cedula,
            c.telefono,
            epara.nombre as para, 
            e.estado,
            t.created_at,
            CONCAT(u.name," ",u.apellido) usuario
            from transferencias t
            inner join clientes c on t.clientes = c.id
            inner join empresas ede on t.empresa_de = ede.id
            inner join empresas epara on t.empresa_para = epara.id
            inner join users u on t.usuario = u.id
            inner join estados e on c.estados = e.id
            where ede.id = '. $empresa .' and u.id = '. $usuario .'
            ');
        }
        return response()->json($enviados);
    }
    // muestra los leads enviados de otra empresa
    public function recepcionenviados(){
        $empresa = Auth::user()->empresas;
        $usuario = Auth::user()->id;
            $enviados = DB::select('
            SELECT 
                c.id,
                c.nombre, 
                c.email, 
                c.cedula,
                c.telefono,
                ede.nombre as de, 
                e.estado,
                t.created_at,
                CONCAT(u.name," ",u.apellido) usuario
            from transferencias t
                inner join clientes c on t.clientes = c.id
                inner join empresas ede on t.empresa_de = ede.id
                inner join empresas epara on t.empresa_para = epara.id
                inner join users u on t.usuario = u.id
                inner join estados e on c.estados = e.id
            where epara.id = '. $empresa .' and c.asignado = 0 
            ');

            return response()->json($enviados);
    }
    // asigna un led a un asesor 
    public function asignar(Request $request){
        $empresa = Auth::user()->empresas;
        $usuario = Auth::user()->id;
        $lead = cliente::find($request['idcliente']);
        $lead->empresas = $empresa;
        $lead->users_id = $request['id'];
        $lead->transferido = 0;
        $lead->asignado = 1;
        $lead->estados = 1;
        $lead->save();
        return response()->json(['mensaje'=>'asignacion completa']);
    }
}
