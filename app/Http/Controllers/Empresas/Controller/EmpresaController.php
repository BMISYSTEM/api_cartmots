<?php
namespace App\Http\Controllers\Empresas\Controller;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Empresas\Implementacion\EmpresasImplement;

class EmpresaController extends Controller
{
    protected  $empresas;
    function __construct(EmpresasImplement $empresas){
        $this->empresas = $empresas;
    }
    function indexEmpresas():object
    {
        $estatus = $this->empresas->indexEmpresas();
        return response()->json($estatus,array_key_exists('erro',$estatus) ? 500 : 200);
    }
}