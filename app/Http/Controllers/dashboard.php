<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class dashboard extends Controller
{
    public function index(Request $request)
    {   
        $inicio = $_GET['inicio'];
        $fin =$_GET['fin'];
        $rol = Auth::user()->rol;
        $usuario = Auth::user()->id;
        $empresa = Auth::user()->empresas;
        $usuarios = '';
        $clientes = '';
        $aprobado = '';
        $rechazado = '';
        $transferencia = '';
        if($rol == 0)
        {
            $usuarios = DB::select("
            select count(u.id) as usuarios from users u 
            inner join empresas em on u.empresas = em.id
            where em.id = ".$empresa." and u.id = ".$usuario."
            ");
            $clientes = DB::select("
            select count(c.id) as clientes from clientes c 
            inner join users u on c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            where u.id = ".$usuario." and em.id =".$empresa." and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $pendiente = DB::select("
            select count(c.id) as pendientes from clientes c
			inner join estados e on c.estados = e.id
            inner join users u on  c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            where e.pendiente = '1' and em.id = ".$empresa." and u.id = ".$usuario." and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $aprobado = DB::select("
            select count(c.id) as aprobados from clientes c
			inner join estados e on c.estados = e.id
            inner join users u on  c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            where e.aprobado = '1' and em.id = ".$empresa." and u.id = ".$usuario." and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $rechazado = DB::select("
            select count(c.id) as rechazados from clientes c
			inner join estados e on c.estados = e.id
            inner join users u on  c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            where e.rechazado = '1' and em.id = ".$empresa." and u.id = ".$usuario." and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $transferencia = DB::select("
                select count(*) as transferencia from transferencias t where t.empresa_para = ". $empresa ."
            ");
            $vitrina = DB::select("
            select count(*) as vitrina from clientes where origen = 1 and users_id = ".$usuario." and  empresas = ". $empresa ." and created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $clientesAsesor = DB::select("
            select count(*) as clientesAsesor from clientes where origen = 3 and users_id = ".$usuario." and  empresas = ". $empresa ." and created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $leads = DB::select("
            select count(*) as leads from clientes where origen = 2 and users_id = ".$usuario." and  empresas = ". $empresa ." and created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
        }else{
            $usuarios = DB::select("
            select count(u.id) as usuarios from users u 
            inner join empresas em on u.empresas = em.id
            where em.id = ".$empresa."
            ");
            $clientes = DB::select("
            select count(c.id) as clientes from clientes c 
            inner join users u on c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            where em.id =".$empresa."  and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $pendiente = DB::select("
            select count(c.id) as pendientes from clientes c
			inner join estados e on c.estados = e.id
            inner join users u on  c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            where e.pendiente = '1' and em.id = ".$empresa." and  c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $aprobado = DB::select("
            select count(c.id) as aprobados from clientes c
			inner join estados e on c.estados = e.id
            inner join users u on  c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            where e.aprobado = '1' and em.id = ".$empresa." and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $rechazado = DB::select("
            select count(c.id) as rechazados from clientes c
			inner join estados e on c.estados = e.id
            inner join users u on  c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            where e.rechazado = '1' and em.id = ".$empresa." and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $transferencia = DB::select("
            select count(*) as transferencia from transferencias t where t.empresa_para = ". $empresa ." and t.created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $vitrina = DB::select("
            select count(*) as vitrina from clientes where origen = 1 and  empresas = ". $empresa ." and created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $clientesAsesor = DB::select("
            select count(*) as clientesAsesor from clientes where origen = 3 and  empresas = ". $empresa ." and created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
            $leads = DB::select("
            select count(*) as leads from clientes where origen = 2 and  empresas = ". $empresa ." and created_at BETWEEN '".$inicio."' AND '".$fin."'
            ");
        }

        return [
                $usuarios[0],
                $clientes[0],
                $pendiente[0],
                $aprobado[0],
                $rechazado[0],
                $transferencia[0],
                $vitrina[0],
                $clientesAsesor[0],
                $leads[0]
            ]
            ;

    }
}
