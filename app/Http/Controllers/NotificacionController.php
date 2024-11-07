<?php

namespace App\Http\Controllers;

use App\Models\notificacion;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    public function index()
    {
        $usuario = Auth()->user()->id;
        $mensajes = notificacion::where('user_id',$usuario)->get();
        return $mensajes;
    }
    public function alertas()
    {   
        $dt = new DateTime();
        $hora = now()->format('H:i:s');
        $empresa = Auth::user()->empresas;
        $user=Auth::user()->id;
        $consulta = "
        select 
        c.id as cliente_id, 
        c.nombre, 
        c.apellido, 
        c.cedula, 
        c.date, 
        c.telefono,
        c.email,
        c.estados,
        c.users_id,
         e.id as estados_id, e.estado, e.created_at, e.updated_at,
         t.comentarios,t.fecha as fecha
         from clientes c 
         inner join users u on c.users_id = u.id
         inner join empresas em on u.empresas = em.id
        inner join estados e on e.id = c.estados
        left join (SELECT max(created_at) as ultima_nota,clientes as clientes FROM notas GROUP BY clientes ) as ult on c.id = ult.clientes
        left join (select proximo_seguimiento fecha,clientes clientes,comentario comentarios,created_at as ult_nota from notas GROUP BY clientes,proximo_seguimiento,comentario,created_at order by proximo_seguimiento desc) as t  on t.clientes = c.id and ult.ultima_nota = t.ult_nota 
        where t.fecha <= '".$dt->format('Y-m-d').' '.$hora."' and em.id = '".$empresa."' and u.id = '".$user."' and e.rechazado <> 1 and e.finalizado = 0 and e.vendido = 0 
        group by c.id,t.comentarios,t.clientes,c.nombre,c.apellido,c.cedula,c.date,c.telefono,c.email,c.estados,c.users_id,e.id,e.estado,e.created_at,e.updated_at,t.fecha 
        ORDER BY  fecha DESC
        ";
        $vista = DB::select($consulta);
        return response()->json($vista);
    }
}
