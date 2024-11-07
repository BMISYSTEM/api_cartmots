<?php

namespace App\Http\Controllers\Proveedor\Interface;


interface ProveedorInterface
{
    function createProveedor($cedula,$nombre,$apellido,$telefono,$telefono2,$email,$direccion):array;

    function updateProveedor($id_proveedor,$cedula,$nombre,$apellido,$telefono,$telefono2,$email,$direccion):array;

    function deleteProveedor($id_proveedor):array;

    function findProveedor($id_proveedor):array;

    function allProveedor():array;

}