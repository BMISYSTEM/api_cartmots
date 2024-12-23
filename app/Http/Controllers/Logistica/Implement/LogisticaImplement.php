<?php
namespace App\Http\Controllers\Logistica\Implement;

use App\Http\Controllers\Logistica\Interface\LogisticaInterface;
use App\Models\logistica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogisticaImplement implements LogisticaInterface
{
    function createMovimiento($placa, $actividad, $motivo, $fecha, $valor, $finalizado,$tipomovimiento,$cargarcuenta,$comentario,$soporte): array
    {
        $idUser = Auth::user()->id;
        try {
            $logisticas = logistica::create(
                [
                    'placa'=>$placa,
                    'actividads'=>$actividad,
                    'motivos'=>$motivo,
                    'fecha'=>$fecha,
                    'valor'=>$valor,
                    'finalizado'=>$finalizado,
                    'empresas'=>Auth::user()->empresas,
                    'tipo_movimiento'=>$tipomovimiento,
                    'cargar_cuenta'=>$cargarcuenta,
                    'comentario'=>$comentario,
                    'soporte'=>$soporte,
                    'usuario'=>$idUser
                ]
            );
            return ['succes'=>'Se guardo correctamente el movimiento'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }

    function updateMovimiento($idMovimiento, $placa, $actividad, $motivo, $fecha, $valor, $finalizado,$operacion): array
    {
        try {
            $logisticas = logistica::find($idMovimiento);
            $logisticas->placa = $placa;
            $logisticas->actividads = $actividad;
            $logisticas->motivos = $motivo;
            $logisticas->fecha = $fecha;
            $logisticas->valor = $valor;
            $logisticas->finalizado = $finalizado;
            $logisticas->operacion = $operacion;
            $logisticas->save();
            return ['succes'=>'Se actualizo correctamente el movimiento'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }

    function deleteMovimiento($idMovimiento): array
    {
        try {
            $logisticas = logistica::find($idMovimiento)->delete();
            return ['succes'=>'Se Elimino correctamente el movimiento'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }

    function findMovimiento($idMovimiento): array
    {
        try {
            // en el ud del movimiento realmente se esta recibiendo la placa 
            $logisticas = DB::select("select v.valor preciovehiculo,lg.comentario,lg.id, lg.placa, lg.fecha, lg.Valor, lg.finalizado, lg.actividads, lg.motivos, lg.empresas, lg.created_at, lg.updated_at,
            at.nombre nombreactividad, mt.nombre nombremotivo,lg.operacion
            from logisticas lg
            inner join actividads at on lg.actividads = at.id
            inner join motivos mt on lg.motivos = mt.id
            inner join vehiculos v on lg.placa = v.placa 
            where lg.placa ='".$idMovimiento."'");
            return ['succes'=>$logisticas];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function allMovimientos(): array
    {
        try {
            $empresa = Auth::user()->empresas;
            $logisticas = DB::select("select u.name as nombre_usuario,u.id as id_user, v.valor preciovehiculo,lg.comentario,lg.cargar_cuenta,lg.id, lg.placa, lg.fecha, lg.Valor, lg.finalizado, lg.actividads, lg.motivos, lg.empresas, lg.created_at, lg.updated_at,
            at.nombre nombreactividad, mt.nombre nombremotivo,lg.operacion
            from logisticas lg
            inner join actividads at on lg.actividads = at.id
            inner join motivos mt on lg.motivos = mt.id
            left join vehiculos v on lg.placa = v.placa 
            left join users u on lg.usuario = u.id
            where lg.empresas ='".$empresa."'");
            return ['succes'=>$logisticas];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
}