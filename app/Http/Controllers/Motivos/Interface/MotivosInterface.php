<?php
namespace App\Http\Controllers\Motivos\Interface;

interface MotivosInterface{
    // crear
    function createMotivo($motivo):array;
    // actualizar 
    function updateMotivo($id,$motivo):array;
    // eliminar
    function deleteMotivo($id):array;
    // consultar 1 
    function findMotivo($id):array;
    // copnsultar todos
    function allMotivo():array;
}