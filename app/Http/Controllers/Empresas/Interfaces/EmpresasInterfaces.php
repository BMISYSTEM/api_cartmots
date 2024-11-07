<?php

namespace App\Http\Controllers\Empresas\Interfaces;

interface EmpresasInterfaces
{
    /** retorna las empresas dentro de la base de datos
     * @return array
     */
    function indexEmpresas():array;
}