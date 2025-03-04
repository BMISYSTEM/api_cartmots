<?php

namespace App\Http\Controllers;

use App\Http\Requests\Nota;
use App\Models\cliente;
use App\Models\estado;
use App\Models\negocio;
use App\Models\notas;
use App\Models\venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotasController extends Controller
{
    
    public function create(Nota $request)
    {
        $nota = $request->validated();
        $users = Auth::user()->id;
        $empresa = Auth::user()->empresas;
        $estado = $request['estado'];
        $cliente = $request['cliente'];
        $infoestado = estado::find($estado);
        if($infoestado->vendido)
        {
            $modulo_costo = DB::select("SELECT modulo_costos from configuraciones where empresa_id = ".$empresa);
            if($modulo_costo[0]->modulo_costos == 0){
                 $ventasinfo = venta::create([  
                    'empresa'=>$empresa,
                    'clientes'=>$nota['cliente'],
                    'usuario'=>$users,
                    'comentario'=>$nota['comentario']
                ]);
            }
            if($modulo_costo[0]->modulo_costos == 1){
                // if(floatval($nota['vtraspaso']) and floatval($nota['vcuotaInicial']) and floatval($nota['vseparacion']) and floatval($nota['vtraspaso'])){
                    $negocios = DB::select("select count(*) cantidad from negocios where vehiculo ='".$nota['placa']."' and finalizado = 0");
                    if($negocios[0]->cantidad == 0 ){
                        $creacionNegocio = negocio::create(
                             [
                                 'vehiculo'=>$nota['placa'] ,
                                 'valorventa'=>$nota['valorventa'],
                                 'porcentajedescuento'=>$nota['porcentaje'], 
                                 'placaretoma' => $nota['placaretoma'] ,
                                 'valorretoma' => $nota['valorretoma'],
                                 'finalizado' => 0,
                                 'cliente'  => $nota['cliente'],
                                 'empresas' => $empresa ,   
                                 'metodopago'=>$nota['metodo'],
                                 'asesor'=>$users,
                                 'vcredito'=>$nota['vcredito'],
                                 'vcuotaInicial'=>$nota['vcuotaInicial'],
                                 'vseparacion'=>$nota['vseparacion'],
                                 'vtraspaso'=>$nota['vtraspaso'],
                                 'asesorios'=>$nota['asesorios'],
                                 'obsequios'=>$nota['obsequios'],
                                 'segundo_precio'=>$nota['segundoPrecio']?? 'sin definir',
                                 'entrega'=>$nota['entrega']?? 'sin definir',
                                 'vendedor'=>$nota['vendedor']?? 'sin definir',
                                 'clausulasAdiccionales'=>$nota['clausulasAdiccionales']?? 'sin definir',
                             ]
                             ); 
                    }else{
                        return response()->json(['error'=>'El negocio no se puede crear debido a que ya existen negocio abierto para esta placa']);
                    }
                    
                // }else{
                //     return "No se guardo la nota verifique los campos inicial,separacion,traspaso, estos campos son numericos no deben contener letras si no aplica coloque 0 (cero)";
                // }
            }
        
        
        }
        $insert = notas::create([
            'comentario' => $nota['comentario'],
            'proximo_seguimiento' => $nota['proximo'].' '.$nota['hora'],
            'hora' => $nota['hora'],
            'estados' => $nota['estado'],
            'clientes' => $nota['cliente'],
            'users'=>$users,
            'empresas'=>$empresa,
            'codigo_negocio'=>isset($creacionNegocio->id) ? $creacionNegocio->id : null
        ]);
    
        // modifica el estado actual del cliente
        $update = cliente::where('id',$cliente)->where('empresas',$empresa)->get();
        $update->toQuery()->update([
            'estados' => $estado
        ]);
        // valida si es una venta lo envia a la tabla de ventas realizadas con el respectivo comentario 
        return "se guardo la nota de forma correcta";
    }
    public function index()
    {
        $cliente = $_GET['id'];
        $empresa = Auth::user()->empresas;
        $vista = DB::select("
        select
        n.id,n.comentario,n.proximo_seguimiento,n.codigo_negocio,
        e.id as id_estado,e.estado,
        u.id as id_users,u.name as nombre_usuario,
        n.clientes,
        e.color as color,
        u.img  as foto,n.created_at
        from notas n
        inner join estados e on e.id = n.estados
        inner join users u on  u.id = n.users
        inner join clientes c on c.id = n.clientes
        where n.empresas = ".$empresa." and n.clientes = ".$cliente."
        ORDER BY  n.created_at DESC
        ");
        return response()->json($vista);
    }    
    public function findPedido(Request $request)
    {
        $request = $request->validate(
            [
                'codigo_negocio' => 'required|exists:negocios,id'
            ],
            [
                'codigo_negocio.required' => 'El negocio es obligatorio',
                'codigo_negocio.exists' => 'El negocio ingresado no existe',
            ]
        );
        $empresa = Auth::user()->empresas;
        $estatus = DB::select("
        select 
        m.nombre,v.linea,v.placa,model.year,
        n.valorventa,n.porcentajedescuento,n.empresas,n.id,n.vendedor,n.entrega,n.segundo_precio,n.clausulasAdiccionales,
        n.vcredito,n.metodopago,n.vcuotaInicial,n.asesorios,n.vseparacion,n.vtraspaso,n.valorretoma,n.obsequios,n.placaretoma,
        cl.nombre as nombre_cliente,cl.apellido as apellido_cliente,cl.cedula as cedula_cliente,cl.telefono as telefono_cliente,cl.email as email_cliente,
        user.name,user.email,user.cedula, v.chasis,
        v.color,
        v.motor,
        v.matricula,
        v.tipo,
        v.servicio,
        v.serie,
        model.year as nombre_modelo,
        v.carroseria
        from negocios n
        inner join users user on n.asesor = user.id and n.empresas = user.empresas
        inner join clientes cl on n.cliente = cl.id and n.empresas = cl.empresas
        inner join vehiculos v on n.vehiculo = v.id and n.empresas = v.empresas
        inner join marcas m on v.marcas = m.id and n.empresas = m.empresas
        inner join modelos model on v.modelos = model.id and n.empresas = model.empresas
        where n.empresas = ".$empresa." and n.id = ".$request['codigo_negocio']."
        ");
        
        return response()->json(['succes'=>$estatus]);
    }
    function deleteNegocioId(Request $request)
    {
        $request = $request->validate(
            [
                'id'=>'required|exists:negocios,id'
            ],
            [
                'id.required'=>'El id es obligatorio',
                'id.exists' => 'El id que desea eliminar no existe'
            ]
        );
        $nota = notas::where('codigo_negocio',$request['id'])->first();
        $nota->comentario = $nota->comentario . '->COSTOS: El pedido fue eliminado desde el area de costos | codigo del pedido:'.$request['id'];
        $nota->codigo_negocio = null;
        $nota->save();
        $estatus = Negocio::find($request['id'])->delete();
        return response()->json(['succes'=>'El negocio fue eliminado con exito']);
        // return response()->json(['succes'=>$nota->comentario]);
    }
    //cierra el negocio desde el modulo de costos en la pantalla de movimiento->cerrar negocio
    function closeNegocio(Request $request)
    {
        $request = $request->validate(
           [
            'id_negocio'=>'required|exists:negocios,id'
            ] 
        );
        $negocio = negocio::find($request['id_negocio']);
        $negocio->finalizado = 1;
        $negocio->save();
        return response()->json(['succes'=>'El negocio fue cerrado con exito']);
    }
}
























