<?php
namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Logistica\Implement\LogisticaImplement;
use App\Models\logistica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogisticaController extends Controller
{
    private $logistica;
    function __construct(LogisticaImplement $implement)
    {
        $this->logistica = $implement;
    }

    function createMovimiento(Request $request):object
    {
        $empresa = Auth::user()->empresas;
        // validacion 
        $request = $request->validate(
            [
                'placa'=>'nullable',
                'actividad'=>'required|exists:actividads,id,empresas,'.$empresa,
                'motivo'=>'required|exists:motivos,id,empresas,'.$empresa,
                'fecha'=>'required|date|date_format:Y-m-d H:i:s',
                'valor'=>'required',
                'finalizado'=>'required',
                'tipo_movimiento'=>'required|numeric',
                'cargar_cuenta'=>'required|numeric',
                'comentario'=>'required',
                'soporte'=>'nullable'
            ],
            [
                'placa.required'=>'La placa es obligatoria',
                'placa.exists' => 'La palca ingresada no existe',
                'actividad.required'=>'La actividad es obligatoria',
                'actividad.exists'=>'La actividad no existe',
                'motivo.required'=>'El motivo es obligatoria ',
                'motivo.exists'=>'El motivo no existe  ',
                'fecha.required'=>'La fecha es obligatoria',
                'fecha.date_format'=>'La fecha no tiene el formato esperado',
                'fecha.date'=>'La fecha no es valida',
                'valor.required'=>'El valor es obligatorio',
                'finalizado.required'=>'El estado es obligatorio',
                'tipo_movimiento.required'=>'Se requiere el tipo de movimiento',
                'cargar_cuenta.required'=>'Se requiere definir a qien se carga la cuenta',
                'comentario.required'=>'El comentario es obligatorio'
            ]
        );
        
        // busqueda por la placa si el movimiento es cualquiera diferente de 4 
        if($request['cargar_cuenta'] != 4)
        {
            if($request['placa'])
            {
                $exist = DB::table('vehiculos')->where('placa',$request['placa'])->where('empresas',$empresa)->first();
                if(!$exist)
                {
                    return response()->json(['error','La placa ingresada no existe'],500);
                }
            }else{
                return response()->json(['error','La placa es obligatoria'],500);
            }
        }
        /* busca el nombre de la empresa */
        $nomempresa = DB::table('empresas')->where('id',$empresa)->first();
        $archivo = '';
        /* si el tipo es 4 se guardara el archivo en la carpeta correspondiente */
        if($request['cargar_cuenta'] == 4 and  $request->hasFile('soporte'))
        {
            $archivo = $request ->file('soporte')->store('public/'.$nomempresa['nombre'].'/documentos');
        }
        // implementacion
        $estatus = $this->logistica->createMovimiento($request['placa'],$request['actividad'],$request['motivo'],$request['fecha'],$request['valor'],$request['finalizado'],$request['tipo_movimiento'],$request['cargar_cuenta'],$request['comentario'],$archivo);
        // $estatus = ['succes'=>true];
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }

    function updateMovimiento(Request $request):object
    {
        $empresa = Auth::user()->empresas;

        $request = $request->validate(
            [
                'id'=>'required|exists:logisticas,id,empresas,'.$empresa,
                'placa'=>'required|exists:vehiculos,placa,empresas,'.$empresa,
                'actividads'=>'required|exists:actividads,id,empresas,'.$empresa,
                'motivos'=>'required|exists:motivos,id,empresas,'.$empresa,
                'fecha'=>'required|date|date_format:Y-m-d H:i:s',
                'Valor'=>'required',
                'finalizado'=>'required',
                'operacion'=>'required'
            ],
            [
                'id.required'=>'el id del movimiento obligatoria',
                'placa.required'=>'La placa es obligatoria',
                'placa.exists' => 'La palca ingresada no existe',
                'actividads.required'=>'La actividad es obligatoria',
                'motivo.required'=>'El motivo es obligatoria ',
                'fecha.required'=>'La fecha es obligatoria',
                'Valor.required'=>'El valor es obligatorio',
                'finalizado.required'=>'El estado es obligatorio',
                'operacion.required'=>'La operacion es obligatoria',
            ]
        );
        $estatus = $this->logistica->updateMovimiento($request['id'],$request['placa'],$request['actividads'],$request['motivos'],$request['fecha'],$request['Valor'],$request['finalizado'],$request['operacion']);
        // $estatus = ['succes'=>true];

        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }

    function deleteMovimiento(Request $request):object
    {
        $request = $request->validate(
            [
                'id_movimiento'=>'required',
            ],
            [
                'id_movimiento.required'=>'el id del movimiento obligatoria',
            ]
        );
        $estatus = $this->logistica->deleteMovimiento($request['id_movimiento']);
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }

    function findMovimiento(Request $request):object
    {
        $request = $request->validate(
            [
                'id_movimiento'=>'required',
            ],
            [
                'id_movimiento.required'=>'el id del movimiento obligatoria',
            ]
        );
        $estatus = $this->logistica->findMovimiento($request['id_movimiento']);
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }
    function allMovimientos():object
    {
       
        $estatus = $this->logistica->allMovimientos();
        // respuesta
        return response()->json($estatus,array_key_exists('error',$estatus) ? 500 : 200);
    }

    // devuelve los costos agregados al vehiculo
    function costosVehiculo(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $request = $request->validate(
            [
                'placa'=>'required|exists:vehiculos,placa'
            ],
            [
                'placa.required'=>'EL placa del vehiculo es obligatorio',
                'placa.exists'=>'EL placa del vehiculo no existe',
            ]
            );

            $estatus = DB::select("
            select  ati.nombre nombreactividad,l.comentario,mt.nombre nombremotivo,l.id, l.placa, l.fecha, l.Valor, l.finalizado, l.actividads, l.motivos, l.empresas, l.created_at, l.updated_at from logisticas l
            inner join actividads ati on l.actividads = ati.id 
            inner join motivos mt on l.motivos = mt.id
            where l.placa ='".$request['placa']."' and l.empresas = '". $empresa ."'  and l.cargar_cuenta = 1
            ");
            $resumen = DB::select("
            select sum(case when l.finalizado = 1 then  l.Valor else 0 end ) pagado,sum(case when l.finalizado = 0 then  l.Valor else 0 end ) debe,v.valor from logisticas l
            inner join vehiculos v on l.placa = v.placa
            where l.placa ='".$request['placa']."' and l.empresas = '". $empresa ."'  and l.cargar_cuenta = 1
            GROUP BY v.valor
            ");
            return response()->json(['succes'=>[['movimientos'=>$estatus],['resumen'=>$resumen]]]);
    }
    // devuelve los costos agregados al proveedor
    function costoProveedor(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $request = $request->validate(
            [
                'placa'=>'required|exists:vehiculos,placa'
            ],
            [
                'placa.required'=>'EL placa del vehiculo es obligatorio',
                'placa.exists'=>'EL placa del vehiculo no existe',
            ]
            );

            $estatus = DB::select("
            select  ati.nombre nombreactividad,l.comentario,mt.nombre nombremotivo,l.id, l.placa, l.fecha, l.Valor, l.finalizado, l.actividads, l.motivos, l.empresas, l.created_at, l.updated_at,l.operacion from logisticas l
            inner join actividads ati on l.actividads = ati.id 
            inner join motivos mt on l.motivos = mt.id
            where l.placa ='".$request['placa']."' and l.empresas = '". $empresa ."'  and l.cargar_cuenta = 3
            order by l.created_at ASC
            ");
            $resumen = DB::select("
            select sum(case when l.finalizado = 1 then  l.Valor else 0 end ) pagado,sum(case when l.finalizado = 0 then  l.Valor else 0 end ) debe,v.valor,v.precio_proveedor,l.operacion from vehiculos v
            LEFT join (select finalizado as finalizado, valor as valor,operacion as operacion,placa as placa,created_at as created_at from logisticas WHERE cargar_cuenta = 3) l on l.placa = v.placa
            where v.placa ='".$request['placa']."' and v.empresas = '". $empresa ."' 
            GROUP BY v.valor,v.precio_proveedor,l.operacion
            ORDER by l.created_at DESC
            ");
            return response()->json(['succes'=>[['movimientos'=>$estatus],['resumen'=>$resumen]]]);
    }
    function costosCliente(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $request = $request->validate(
            [
                'placa'=>'required|exists:vehiculos,placa'
            ],
            [
                'placa.required'=>'EL placa del vehiculo es obligatorio',
                'placa.exists'=>'EL placa del vehiculo no existe',
            ]
            );

            $estatus = DB::select("
            select  ati.nombre nombreactividad,l.comentario,mt.nombre nombremotivo,l.id, l.placa, l.fecha, l.Valor, l.finalizado, l.actividads, l.motivos, l.empresas, l.created_at, l.updated_at,l.operacion from logisticas l
            inner join actividads ati on l.actividads = ati.id 
            inner join motivos mt on l.motivos = mt.id
            where l.placa ='".$request['placa']."' and l.empresas = '". $empresa ."'  and l.cargar_cuenta = 2
            ");
            $resumen = DB::select("
            select sum(case when l.finalizado = 1 then  l.Valor else 0 end ) pagado,sum(case when l.finalizado = 0 then  l.Valor else 0 end ) debe,v.valor from logisticas l
            inner join vehiculos v on l.placa = v.placa
            where l.placa ='".$request['placa']."' and l.empresas = '". $empresa ."'  and l.cargar_cuenta = 2
            GROUP BY v.valor
            ");
            $negocio = DB::select("
            select 
            ng.id as id_negocio,ng.vehiculo,ng.valorventa,ng.porcentajedescuento,ng.placaretoma,ng.valorretoma,ng.finalizado,ng.cliente,ng.empresas,ng.metodopago,ng.asesor,ng.asesor,ng.vcredito,ng.vcuotaInicial,ng.vseparacion,ng.asesorios,ng.obsequios,ng.vtraspaso,
            v.id id,v.placa,v.kilometraje,v.marcas,v.modelos,v.estados,v.valor,v.peritaje,v.empresas,v.disponibilidad,v.caja,v.version,v.linea,v.soat,v.soat,v.tecnomecanica,v.proveedor
            from negocios ng 
            inner join vehiculos v on ng.vehiculo = v.id 
            where v.placa = '".$request['placa']."'
            ");
            return response()->json(['succes'=>[['movimientos'=>$estatus],['resumen'=>$resumen],['negocio'=>$negocio]]]);
    }
    // devuelve los costos agregados al cliente
    function allnegocios(Request $request)
    {
        $empresa = Auth::user()->empresas;

            $estatus = DB::select("
            select c.nombre,c.apellido,c.cedula,c.telefono,c.email,ng.cliente,ng.id as id_negocio,ng.finalizado  from negocios ng 
            inner join clientes c on ng.cliente = c.id
            where ng.empresas = '". $empresa ."'
            GROUP by c.nombre,c.apellido,c.cedula,c.telefono,c.email,ng.cliente,ng.id,ng.finalizado
            order by ng.created_at desc
            ");
            return response()->json(['succes'=>$estatus]);
    }
    function allnegociosvehiculos(Request $request)
    {
        $empresa = Auth::user()->empresas;

            $estatus = DB::select("
            select c.nombre,c.apellido,c.cedula,c.telefono,c.email, 
            v.placa,m.nombre,v.linea,v.valor,v.version,ng.cliente,ng.id id_negocio
            from negocios ng 
            inner join clientes c on ng.cliente = c.id
            inner join vehiculos v on ng.vehiculo = v.id
            inner join marcas m on v.marcas = m.id
            where ng.empresas = '".$empresa."'
            ");
            return response()->json(['succes'=>$estatus]);
    }
}