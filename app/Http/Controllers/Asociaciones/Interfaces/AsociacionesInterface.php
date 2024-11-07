<?php

namespace App\Http\Controllers\Asociaciones\Interfaces;

interface AsociacionesInterface {
    /** Crea las nuevas solicitudes de asociaciones por defecto se pone en 0 aceptado y rechazado lo que da un pendiente 
     * @param int $empresa_solicitante
     * @param int $empresa_receptora
     * @param int $vehiculo
     * @param int $clientes
     * 
     * @return array
     */
    function createAsociacion( int $empresa_receptora,int $vehiculo, int $clientes):array;

    /** devuelve las solicitudes que he enviado como empresa
     * @return array
     */
    function indexsolicitudes():array;

    /** devuelve las solicitudes que me han enviado
     * @return array
     */
    function indexsolicitudesRecibidas():array;

    /** devuelve todas las solicitudes que se han aprobado ose ya existe una asociacion
     * @return array
     */
    function indexasociaciones():array;

    /** cancela envio de solicitudes
     * @return array
     * @param int $id id de la solicitud 
     */
    function cancelarEnvioSolicitud(int $id):array;

    /** actualiza los permisos de la solicitud
     * @param int $id
     * @param int $vehiculo
     * @param int $clientes
     * 
     * @return array
     */
    function updateSolicitud(int $id,int $vehiculo,int $clientes):array;
    /** Aprueba una solicitud
     * @param int $id
     * 
     * @return array
     */
    function AprobarSolicitud(int $id):array;

}