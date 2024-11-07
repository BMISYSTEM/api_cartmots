<?php
namespace App\Http\Controllers\Empresas\Implementacion;

use App\Http\Controllers\Empresas\Interfaces\EmpresasInterfaces;
use App\Models\empresa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class EmpresasImplement implements EmpresasInterfaces
{
      function indexEmpresas():array
      {
        try {
          $empresaactiva = Auth::user()->empresas;
          // consulta las empresas diferentes a la activa y que no este en la tabla de asociaciones enviadas desde la planta activa 
            $empresa = DB::select('
            SELECT * 
            FROM empresas 
            WHERE id <> '.$empresaactiva.' 
            AND id NOT IN (SELECT empresa_receptora FROM solicitud_asociaciones where empresa_solicitante = '.$empresaactiva.')
            ');
            return ['succes'=>$empresa];
        } catch (\Throwable $th) {
            return ['succes'=>'Error inesperado en el servidor'];
        }
      }
}