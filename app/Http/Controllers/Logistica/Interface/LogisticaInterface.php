<?php
namespace App\Http\Controllers\Logistica\Interface;

interface LogisticaInterface{

    function createMovimiento($placa,$actividad,$motivo,$fecha,$valor,$finalizado,$tipomovimiento,$cargarcuenta,$comentario,$soporte):array;

    function updateMovimiento($idMovimiento,$placa,$actividad,$motivo,$fecha,$valor,$finalizado,$operacion):array;

    function deleteMovimiento($idMovimiento):array;

    function findMovimiento($idMovimiento):array;

    function allMovimientos():array;
}