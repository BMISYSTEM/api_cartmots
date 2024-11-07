<?php 

namespace App\Http\Controllers\Actividades\Interface;

interface ActividadesInterface{
    // crear
    function createActividad($nombre):array;
    // editar
    function updateActividad($id,$nombre):array;
    // eliminar 
    function deleteActividad($id):array;
    // consultar 1 
    function findActividad($id):array;
    // consultar todos
    function allActividad():array;
}
