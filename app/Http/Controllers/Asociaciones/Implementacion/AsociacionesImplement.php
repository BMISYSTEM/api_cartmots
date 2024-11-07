<?php

namespace App\Http\Controllers\Asociaciones\Implementacion;

use App\Http\Controllers\Asociaciones\Interfaces\AsociacionesInterface;
use App\Models\solicitud_asociacione;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AsociacionesImplement implements AsociacionesInterface
{
    function createAsociacion(int $empresa_receptora, int $vehiculo, int $clientes): array
    {
        try {
            $empresa_solicitante = Auth::user()->empresas;
            $estatus = solicitud_asociacione::create(
                [
                    'empresa_solicitante' => $empresa_solicitante,
                    'empresa_receptora' => $empresa_receptora,
                    'vehiculo' => $vehiculo,
                    'clientes' => $clientes
                ]
            );
            return ['succes'=>'Solicitud registrada con exito'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    function indexsolicitudes(): array
    {
        try {
            $empresa_solicitante = Auth::user()->empresas;
            $solicitudes = DB::select('
                select s.id,
                er.nombre as empresareceptora,
                er.id empresareceptoraid,
                es.nombre as empresasolicitante,
                es.id empresasolicitanteid,
                s.vehiculo,
                s.clientes,
                s.created_at,
                s.aceptado,
                s.rechazado
                from solicitud_asociaciones s
                inner join empresas er on s.empresa_receptora = er.id
                inner join empresas es on s.empresa_solicitante = es.id
                where  s.empresa_solicitante ='.$empresa_solicitante);
            return ['succes'=>$solicitudes];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    function indexsolicitudesRecibidas(): array
    {
        try {
            $empresa_solicitante = Auth::user()->empresas;
            $solicitudes = DB::select('
            select s.id,
            er.nombre as empresareceptora,
            er.id empresareceptoraid,
            es.nombre as empresasolicitante,
            es.id empresasolicitanteid,
            s.vehiculo,
            s.clientes,
            s.created_at,
            s.aceptado,
            s.rechazado
            from solicitud_asociaciones s
            inner join empresas er on s.empresa_receptora = er.id
            inner join empresas es on s.empresa_solicitante = es.id
            where  s.empresa_receptora ='.$empresa_solicitante);
            return ['succes'=>$solicitudes];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    function indexasociaciones(): array
    {
        try {
            $empresa_solicitante = Auth::user()->empresas;
            $solicitudes = DB::select('
                select s.id,
                er.nombre as empresareceptora,
                er.id empresareceptoraid,
                es.nombre as empresasolicitante,
                es.id empresasolicitanteid,
                s.vehiculo,
                s.clientes,
                s.created_at,
                s.aceptado,
                s.rechazado
                from solicitud_asociaciones s
                inner join empresas er on s.empresa_receptora = er.id
                inner join empresas es on s.empresa_solicitante = es.id
                where  (s.empresa_solicitante ='.$empresa_solicitante .' or s.empresa_receptora ='.$empresa_solicitante .') 
                 and s.aceptado = 1');
            return ['succes'=>$solicitudes];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }
    function cancelarEnvioSolicitud(int $id): array
    {
        try {
            $estatus = solicitud_asociacione::find($id);
            $estatus->delete();
            return ['succes'=>'Solicitud cancelada con exito'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }
    function updateSolicitud(int $id, int $vehiculo, int $clientes): array
    {
        try {
            $estatus = solicitud_asociacione::find($id);
            $estatus->vehiculo = $vehiculo;
            $estatus->clientes = $clientes;
            $estatus->save();
            return ['succes'=>'Solicitud actualizada con exito'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }
    function AprobarSolicitud(int $id): array
    {
        try {
            $solicitud = solicitud_asociacione::find($id);
            if($solicitud->empresa_receptora === Auth::user()->empresas)
            {
                $solicitud->aceptado = 1;
                $solicitud->save();
            }
            return ['succes'=>'Solicitud aprobada con exito, ahora podran intercambiar informacion segun la configuracion'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }
}