<?php

namespace App\Http\Controllers\Logistica;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Logistica\Implement\LogisticaImplement;
use App\Models\logistica;
use App\Models\negocio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

use function PHPUnit\Framework\isEmpty;

class LogisticaController extends Controller
{
    private $logistica;
    function __construct(LogisticaImplement $implement)
    {
        $this->logistica = $implement;
    }


    function createContrato(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $users = Auth::user()->id;
        $negocios = DB::select("select count(*) cantidad from negocios where vehiculo ='".$request['placa']."' and finalizado = 0");
        if($negocios[0]->cantidad == 0 ){
            $creacionNegocio = negocio::create(
                 [
                     'vehiculo'=>$request['placa'] ,
                     'valorventa'=>$request['valorventa'] ?? 0,
                     'porcentajedescuento'=>$request['porcentaje'] ?? 0, 
                     'placaretoma' => $request['placaretoma'] ?? "" ,
                     'valorretoma' => $request['valorretoma'] ?? 0,
                     'finalizado' => 0,
                     'cliente'  => $request['cliente'],
                     'empresas' => $empresa ,   
                     'metodopago'=>$request['metodo'] ?? " ",
                     'asesor'=>$users,
                     'vcredito'=>$request['vcredito'] ?? "",
                     'vcuotaInicial'=>$request['vcuotaInicial'] ?? 0,
                     'vseparacion'=>$request['vseparacion'] ?? 0,
                     'vtraspaso'=>$request['vtraspaso'] ?? 0,
                     'asesorios'=>$request['asesorios'] ?? "",
                     'obsequios'=>$request['obsequios'] ?? "",
                     'segundo_precio'=>$request['segundoPrecio']?? 'sin definir',
                     'entrega'=>$request['entrega']?? 'sin definir',
                     'vendedor'=>$request['vendedor']?? 'sin definir',
                     'clausulasAdiccionales'=>$request['clausulasAdiccionales']?? 'sin definir',
                 ]
                 ); 
            return response()->json(['succes'=>'El negocio Se creo de forma correcta']);

        }else{
            return response()->json(['error'=>'El negocio no se puede crear debido a que ya existen negocio abierto para esta placa']);
        }
    }
    function deleteContrato(Request $request){
        try {
            $contrato = negocio::find($request['id']);
            $movimientos = logistica::where('placa',$request['placa'])->where('cargar_cuenta',2)->get();
            if(!$movimientos->isEmpty()){
                return response()->json(['error'=>'No se puede eliminar este negocio debido a que ya tiene movimientos causados, solicite eliminacion desde el modulo de costos.']);
            }else{
                $contrato->delete();
                return response()->json(['succes'=>'Se elimino de forma correcta.']);
            }
        } catch (\Throwable $th) {
            return response()->json(['error'=>'Error generado en el servidor, contacte con soporte'.$th->getMessage()]);

        }
    }
    function createMovimiento(Request $request): object
    {
        $empresa = Auth::user()->empresas;
        // validacion 
        $requestv = $request->validate(
            [
                'placa' => 'nullable',
                'actividad' => 'required|exists:actividads,id,empresas,' . $empresa,
                'motivo' => 'required|exists:motivos,id,empresas,' . $empresa,
                'fecha' => 'required|date|date_format:Y-m-d H:i:s',
                'valor' => 'required',
                'finalizado' => 'required',
                'tipo_movimiento' => 'required|numeric',
                'cargar_cuenta' => 'required|numeric',
                'comentario' => 'required',
                'soporte' => 'nullable',
                'soporte' => 'nullable'
            ],
            [
                'placa.required' => 'La placa es obligatoria',
                'placa.exists' => 'La palca ingresada no existe',
                'actividad.required' => 'La actividad es obligatoria',
                'actividad.exists' => 'La actividad no existe',
                'motivo.required' => 'El motivo es obligatoria ',
                'motivo.exists' => 'El motivo no existe  ',
                'fecha.required' => 'La fecha es obligatoria',
                'fecha.date_format' => 'La fecha no tiene el formato esperado',
                'fecha.date' => 'La fecha no es valida',
                'valor.required' => 'El valor es obligatorio',
                'finalizado.required' => 'El estado es obligatorio',
                'tipo_movimiento.required' => 'Se requiere el tipo de movimiento',
                'cargar_cuenta.required' => 'Se requiere definir a qien se carga la cuenta',
                'comentario.required' => 'El comentario es obligatorio'
            ]
        );

        // busqueda por la placa si el movimiento es cualquiera diferente de 4 
        if ($requestv['cargar_cuenta'] != 4) {
            if ($requestv['placa']) {
                $exist = DB::table('vehiculos')->where('placa', $requestv['placa'])->where('empresas', $empresa)->first();
                if (!$exist) {
                    return response()->json(['placa' => 'La placa ingresada no existe'], 500);
                }
            } else {
                return response()->json(['placa' => 'La placa es obligatoria'], 500);
            }
        }
        /* Validacion por saldos si el movimiento es de tipo 4  de enviar el error se debera asignar un valor por el administrador */
        if($requestv['cargar_cuenta'] == 4){
            $valorDisponible = DB::select('select sum(valor) as sum from monto_usuarios where id_user = '.Auth::user()->id .' and empresas = '.$empresa);
            $valorMovimientos = DB::select('select sum(Valor) as sum from logisticas where cargar_cuenta = 4  and usuario = '.Auth::user()->id . ' and empresas = '.$empresa);
            $total = $valorDisponible[0]->sum - $valorMovimientos[0]->sum;
            if($total < $requestv['valor']){
                return response()->json(['valor' => 'El valor ingresado supera el monto disponible, Comunicater con el administrador de costos'], 500);
            }
        }
        /* busca el nombre de la empresa */
        $nomempresa = DB::table('empresas')->where('id', $empresa)->first();
        $archivo = '';
        /* si el tipo es 4 se guardara el archivo en la carpeta correspondiente */
        if ($request->input('cargar_cuenta') == 4 && $request->hasFile('soporte')) {
            $archivo = $request->file('soporte')->store('public/' . $nomempresa->nombre  . '/documentos');
        }
        // implementacion
        $estatus = $this->logistica->createMovimiento($requestv['placa'], 
        $requestv['actividad'], $requestv['motivo'], $requestv['fecha'], 
        $requestv['valor'], $requestv['finalizado'], $requestv['tipo_movimiento'],
         $requestv['cargar_cuenta'], $requestv['comentario'], $archivo);
        // $estatus = ['succes'=>true];
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }

    function updateMovimiento(Request $request): object
    {
        $empresa = Auth::user()->empresas;

        $request = $request->validate(
            [
                'id' => 'required|exists:logisticas,id,empresas,' . $empresa,
                'placa' => 'nullable',
                'actividads' => 'required|exists:actividads,id,empresas,' . $empresa,
                'motivos' => 'required|exists:motivos,id,empresas,' . $empresa,
                'fecha' => 'required|date|date_format:Y-m-d H:i:s',
                'Valor' => 'required',
                'finalizado' => 'required',
                'operacion' => 'nullable'
            ],
            [
                'id.required' => 'el id del movimiento obligatoria',
                'placa.required' => 'La placa es obligatoria',
                'placa.exists' => 'La palca ingresada no existe',
                'actividads.required' => 'La actividad es obligatoria',
                'motivo.required' => 'El motivo es obligatoria ',
                'fecha.required' => 'La fecha es obligatoria',
                'Valor.required' => 'El valor es obligatorio',
                'finalizado.required' => 'El estado es obligatorio',
                'operacion.required' => 'La operacion es obligatoria',
            ]
        );
        $estatus = $this->logistica->updateMovimiento($request['id'], $request['placa'], $request['actividads'], $request['motivos'], $request['fecha'], $request['Valor'], $request['finalizado'], $request['operacion'] ?? 0);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }

    function deleteMovimiento(Request $request): object
    {
        $request = $request->validate(
            [
                'id_movimiento' => 'required',
            ],
            [
                'id_movimiento.required' => 'el id del movimiento obligatoria',
            ]
        );
        $estatus = $this->logistica->deleteMovimiento($request['id_movimiento']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }

    function findMovimiento(Request $request): object
    {
        $request = $request->validate(
            [
                'id_movimiento' => 'required',
            ],
            [
                'id_movimiento.required' => 'el id del movimiento obligatoria',
            ]
        );
        $estatus = $this->logistica->findMovimiento($request['id_movimiento']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    function allMovimientos(): object
    {

        $estatus = $this->logistica->allMovimientos();
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }

    // devuelve los costos agregados al vehiculo
    function costosVehiculo(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $request = $request->validate(
            [
                'placa' => 'required|exists:vehiculos,placa'
            ],
            [
                'placa.required' => 'EL placa del vehiculo es obligatorio',
                'placa.exists' => 'EL placa del vehiculo no existe',
            ]
        );

        $estatus = DB::select("
            select  ati.nombre nombreactividad,l.comentario,mt.nombre nombremotivo,l.id, l.placa, l.fecha, l.Valor, l.finalizado, l.actividads, l.motivos, l.empresas, l.created_at, l.updated_at from logisticas l
            inner join actividads ati on l.actividads = ati.id 
            inner join motivos mt on l.motivos = mt.id
            where l.placa ='" . $request['placa'] . "' and l.empresas = '" . $empresa . "'  and l.cargar_cuenta = 1
            ");
        $resumen = DB::select("
            select sum(case when l.finalizado = 1 then  l.Valor else 0 end ) pagado,sum(case when l.finalizado = 0 then  l.Valor else 0 end ) debe,v.valor from logisticas l
            inner join vehiculos v on l.placa = v.placa
            where l.placa ='" . $request['placa'] . "' and l.empresas = '" . $empresa . "'  and l.cargar_cuenta = 1
            GROUP BY v.valor
            ");
        return response()->json(['succes' => [['movimientos' => $estatus], ['resumen' => $resumen]]]);
    }
    // devuelve los costos agregados al proveedor
    function costoProveedor(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $request = $request->validate(
            [
                'placa' => 'required|exists:vehiculos,placa'
            ],
            [
                'placa.required' => 'EL placa del vehiculo es obligatorio',
                'placa.exists' => 'EL placa del vehiculo no existe',
            ]
        );

        $estatus = DB::select("
            select  ati.nombre nombreactividad,l.comentario,mt.nombre nombremotivo,l.id, l.placa, l.fecha, l.Valor, l.finalizado, l.actividads, l.motivos, l.empresas, l.created_at, l.updated_at,l.operacion from logisticas l
            inner join actividads ati on l.actividads = ati.id 
            inner join motivos mt on l.motivos = mt.id
            where l.placa ='" . $request['placa'] . "' and l.empresas = '" . $empresa . "'  and l.cargar_cuenta = 3
            order by l.created_at ASC
            ");
        $resumen = DB::select("
            select sum(case when l.finalizado = 1 then  l.Valor else 0 end ) pagado,sum(case when l.finalizado = 0 then  l.Valor else 0 end ) debe,v.valor,v.precio_proveedor,l.operacion from vehiculos v
            LEFT join (select finalizado as finalizado, valor as valor,operacion as operacion,placa as placa,created_at as created_at from logisticas WHERE cargar_cuenta = 3) l on l.placa = v.placa
            where v.placa ='" . $request['placa'] . "' and v.empresas = '" . $empresa . "' 
            GROUP BY v.valor,v.precio_proveedor,l.operacion
            ORDER by l.created_at DESC
            ");
        return response()->json(['succes' => [['movimientos' => $estatus], ['resumen' => $resumen]]]);
    }
    function costosCliente(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $request = $request->validate(
            [
                'placa' => 'required|exists:vehiculos,placa'
            ],
            [
                'placa.required' => 'EL placa del vehiculo es obligatorio',
                'placa.exists' => 'EL placa del vehiculo no existe',
            ]
        );

        $estatus = DB::select("
            select  ati.nombre nombreactividad,l.comentario,mt.nombre nombremotivo,l.id, l.placa, l.fecha, l.Valor, l.finalizado, l.actividads, l.motivos, l.empresas, l.created_at, l.updated_at,l.operacion from logisticas l
            inner join actividads ati on l.actividads = ati.id 
            inner join motivos mt on l.motivos = mt.id
            where l.placa ='" . $request['placa'] . "' and l.empresas = '" . $empresa . "'  and l.cargar_cuenta = 2
            ");
        $resumen = DB::select("
            select sum(case when l.finalizado = 1 then  l.Valor else 0 end ) pagado,sum(case when l.finalizado = 0 then  l.Valor else 0 end ) debe,v.valor from logisticas l
            inner join vehiculos v on l.placa = v.placa
            where l.placa ='" . $request['placa'] . "' and l.empresas = '" . $empresa . "'  and l.cargar_cuenta = 2
            GROUP BY v.valor
            ");
        $negocio = DB::select("
            select 
            ng.id as id_negocio,ng.vehiculo,ng.valorventa,ng.porcentajedescuento,ng.placaretoma,ng.valorretoma,ng.finalizado,ng.cliente,ng.empresas,ng.metodopago,ng.asesor,ng.asesor,ng.vcredito,ng.vcuotaInicial,ng.vseparacion,ng.asesorios,ng.obsequios,ng.vtraspaso,
            v.id id,v.placa,v.kilometraje,v.marcas,v.modelos,v.estados,v.valor,v.peritaje,v.empresas,v.disponibilidad,v.caja,v.version,v.linea,v.soat,v.soat,v.tecnomecanica,v.proveedor
            from negocios ng 
            inner join vehiculos v on ng.vehiculo = v.id 
            where v.placa = '" . $request['placa'] . "'
            ");
        return response()->json(['succes' => [['movimientos' => $estatus], ['resumen' => $resumen], ['negocio' => $negocio]]]);
    }
    // devuelve los costos agregados al cliente
    function allnegocios(Request $request)
    {
        $empresa = Auth::user()->empresas;

        $estatus = DB::select("
            select c.nombre,c.apellido,c.cedula,c.telefono,c.email,ng.cliente,ng.id as id_negocio,ng.finalizado  from negocios ng 
            inner join clientes c on ng.cliente = c.id
            where ng.empresas = '" . $empresa . "'
            GROUP by c.nombre,c.apellido,c.cedula,c.telefono,c.email,ng.cliente,ng.id,ng.finalizado
            order by ng.created_at desc
            ");
        return response()->json(['succes' => $estatus]);
    }
    function allnegociosvehiculos(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $idCliente = $request->query('id');
        $sql = "
            select c.nombre,c.apellido,c.cedula,c.telefono,c.email, 
            v.placa,m.nombre,v.linea,v.valor,v.version,ng.cliente,ng.id id_negocio
            from negocios ng 
            inner join clientes c on ng.cliente = c.id
            inner join vehiculos v on ng.vehiculo = v.id
            inner join marcas m on v.marcas = m.id
            where ng.empresas = '" . $empresa . "'
        ";
        if(!empty($idCliente)){
            $sql .= " and c.id = '".$idCliente."'";
        }
        $estatus = DB::select($sql);
        return response()->json(['succes' => $estatus]);
    }
    // retorna la informacion de un solo negocio
    function findNegocio(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $idNegocio = $request->query('negocio');
        $negocio = negocio::find($idNegocio);
        return response()->json(['succes' => $negocio]);
    }
    // edita la informacion de un negocio
    function editNegocio(Request $request){

        $negocio = negocio::find($request['id']);                                                       
        $negocio->vehiculo = $request['placa'] ?? $negocio->vehiculo;
        $negocio->valorventa = $request['valorventa'] ?? $negocio->valorventa;
        $negocio->porcentajedescuento = $request['porcentaje']  ?? $negocio->porcentajedescuento;
        $negocio->placaretoma = $request['placaretoma'] ?? $negocio->placaretoma;
        $negocio->valorretoma = $request['valorretoma'] ??  $negocio->valorretoma;
        $negocio->finalizado = $request['finalizado'] ?? $negocio->finalizado;
        $negocio->cliente = $request['cliente'] ?? $negocio->cliente;
        $negocio->empresas = $request['empresas'] ?? $negocio->empresas;
        $negocio->metodopago = $request['metodo'] ?? $negocio->metodopago;
        $negocio->asesor = $request['asesor'] ?? $negocio->asesor;
        $negocio->vcredito = $request['vcredito'] ?? $negocio->vcredito;
        $negocio->vcuotaInicial = $request['vcuotaInicial'] ?? $negocio->vcuotaInicial;
        $negocio->vseparacion = $request['vseparacion'] ?? $negocio->vseparacion;
        $negocio->vtraspaso = $request['vtraspaso'] ?? $negocio->vtraspaso;
        $negocio->asesorios = $request['asesorios'] ?? $negocio->asesorios;
        $negocio->obsequios = $request['obsequios'] ?? $negocio->obsequios;
        $negocio->segundo_precio = $request['segundoPrecio'] ?? $negocio->segundo_precio;;
        $negocio->vendedor = $request['vendedor'] ?? $negocio->vendedor;
        $negocio->entrega = $request['entrega'] ?? $negocio->entrega;
        $negocio->clausulasAdiccionales = $request['clausulasAdiccionales'] ?? $negocio->clausulasAdiccionales;;
        $negocio->save();
        return response()->json(['succes' => 'Negocio actualizado correctamente']);
    }

    /* esta funcion permite crear un valor a la tabla de monto de usuario para despues cruzarlo con los movimientos realizados en logistica  */
    function createMontoUsuario(Request $request): object
    {
        $empresa = Auth::user()->empresas;
        $request = $request->validate(
            [
                'valor' => 'required',
                'usuario_id' => 'required',
            ],
            [
                'valor.required' => 'El valor es obligatorio',
                'usuario_id.required' => 'El usuario es obligatorio',
            ]
        );
        try {
            $monto = DB::table('monto_usuarios')->insert([
                'valor' => $request['valor'],
                'id_user' => $request['usuario_id'],
                'empresas' => $empresa
            ]);
            if($monto){
                $estatus = ['succes'=>'Monto ingresado correctamente'];
            }else{
                $estatus = ['error'=>'No se pudo ingresar el monto'];
            }
        } catch (\Throwable $th) {
            $estatus = ['error' => $th->getMessage()];
        }
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    /* consulta todos los valores asignados a un usuario */
    function indexMontosUsuarios(Request $request): object
    {
        $empresa = Auth::user()->empresas;
        $id = $request->query('id');
        $estatus = DB::table('monto_usuarios')->where('empresas', $empresa)->where('id_user',$id)->get();
        return response()->json(['succes' => $estatus]);
    }
    /* elimina un monto asignado */
    function deleteMontoUsuario(Request $request): object
    {
        $request = $request->validate(
            [
                'id' => 'required',
            ],
            [
                'id.required' => 'el id del monto es obligatoria',
            ]
        );
        try {
            //code...
            $delete = DB::table('monto_usuarios')->where('id', $request['id'])->delete();
            if($delete){
                $estatus = ['succes'=>'Monto eliminado correctamente'];
            }else{
                $estatus = ['error'=>'No se pudo eliminar el monto'];
            }
        } catch (\Throwable $th) {
            //throw $th;
            $estatus = ['error' => $th->getMessage()];
        }
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    /* actualiza un monto asignado */
    function updateMontoUsuario(Request $request): object
    {
        $empresa = Auth::user()->empresas;
        $request = $request->validate(
            [
                'id' => 'required',
                'valor' => 'required',
                'usuario_id' => 'required',
            ],
            [
                'id.required' => 'el id del monto es obligatoria',
                'valor.required' => 'El valor es obligatorio',
                'usuario_id.required' => 'El usuario es obligatorio',
            ]
        );
        try {
            //code...
            $update = DB::table('monto_usuarios')->where('id', $request['id'])->update([
                'valor' => $request['valor'],
                'usuario_id' => $request['usuario_id'],
                'empresas' => $empresa
            ]);
            if($update){
                $estatus = ['succes'=>'Monto actualizado correctamente'];
            }else{
                $estatus = ['error'=>'No se pudo actualizar el monto'];
            }
        } catch (\Throwable $th) {
            //throw $th;
            $estatus = ['error' => $th->getMessage()];
        }
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    /* consulta todos los movimientos de logistica para despues cruzarlos con los montos asignados  */
    function indexMovimientosUsuario(Request $request)
    {
        $id = $request->query('id');
        $empresa = Auth::user()->empresas;
        try {
            $movimientos = DB::select("select l.*,a.nombre as nombre_actividad,m.nombre as nombre_motivo from logisticas l 
                                        inner join actividads a on l.actividads = a.id
                                        inner join motivos m on l.motivos = m.id
                                        where l.cargar_cuenta = 4  and l.empresas = '" . $empresa . "' and l.usuario = '" . $id . "' order by l.created_at desc");
            return response()->json(['succes' => $movimientos]);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }
}
