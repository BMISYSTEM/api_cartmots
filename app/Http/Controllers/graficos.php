<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class graficos extends Controller
{
    public function barra()
    {
        $empresa = Auth::user()->empresas;
        $user_id = Auth::user()->id;
        $modulo_costo = DB::select("SELECT modulo_costos from configuraciones where empresa_id = ".$empresa);
        if($modulo_costo[0]->modulo_costos == 0 )
        {
            $Query = '
            select 
                count(*) as registros,
                (select count(*) from ventas where MONTH(created_at) = 1    and empresa = '.$empresa.') as enero,
                (select count(*) from ventas where MONTH(created_at) = 2    and empresa = '.$empresa.') as febrero,
                (select count(*) from ventas where MONTH(created_at) = 3    and empresa = '.$empresa.') as marzo,
                (select count(*) from ventas where MONTH(created_at) = 4    and empresa = '.$empresa.') as abril,
                (select count(*) from ventas where MONTH(created_at) = 5    and empresa = '.$empresa.') as mayo,
                (select count(*) from ventas where MONTH(created_at) = 6    and empresa = '.$empresa.') as junio,
                (select count(*) from ventas where MONTH(created_at) = 7    and empresa = '.$empresa.') as julio,
                (select count(*) from ventas where MONTH(created_at) = 8    and empresa = '.$empresa.') as agosto,
                (select count(*) from ventas where MONTH(created_at) = 9    and empresa = '.$empresa.') as septiembre,
                (select count(*) from ventas where MONTH(created_at) = 10   and empresa = '.$empresa.') as optubre,
                (select count(*) from ventas where MONTH(created_at) = 11   and empresa = '.$empresa.') as noviembre,
                (select count(*) from ventas where MONTH(created_at) = 12   and empresa = '.$empresa.') as diciembre
            from ventas where empresa = '.$empresa.'
            ';
        }else{
            $Query = '
                select 
                count(*) as registros,
                (select count(*) from negocios where MONTH(updated_at) = 1    and empresas = '.$empresa.' and finalizado = 1) as enero,
                (select count(*) from negocios where MONTH(updated_at) = 2    and empresas = '.$empresa.' and finalizado = 1) as febrero,
                (select count(*) from negocios where MONTH(updated_at) = 3    and empresas = '.$empresa.' and finalizado = 1) as marzo,
                (select count(*) from negocios where MONTH(updated_at) = 4    and empresas = '.$empresa.' and finalizado = 1) as abril,
                (select count(*) from negocios where MONTH(updated_at) = 5    and empresas = '.$empresa.' and finalizado = 1) as mayo,
                (select count(*) from negocios where MONTH(updated_at) = 6    and empresas = '.$empresa.' and finalizado = 1) as junio,
                (select count(*) from negocios where MONTH(updated_at) = 7    and empresas = '.$empresa.' and finalizado = 1) as julio,
                (select count(*) from negocios where MONTH(updated_at) = 8    and empresas = '.$empresa.' and finalizado = 1) as agosto,
                (select count(*) from negocios where MONTH(updated_at) = 9    and empresas = '.$empresa.' and finalizado = 1) as septiembre,
                (select count(*) from negocios where MONTH(updated_at) = 10   and empresas = '.$empresa.' and finalizado = 1) as optubre,
                (select count(*) from negocios where MONTH(updated_at) = 11   and empresas = '.$empresa.' and finalizado = 1) as noviembre,
                (select count(*) from negocios where MONTH(updated_at) = 12   and empresas = '.$empresa.' and finalizado = 1) as diciembre
                from negocios where empresas = '.$empresa.'
            ';
        }
        
        $vista = DB::select($Query);
        return response()->json($vista);
    }
}
