<?php

namespace App\Http\Controllers\Vehiculos\Interfaces;

interface VehiculoInterface
{
    /** Creacion de un nievo vehiculo
     * @param string $placa
     * @param int $kilometro
     * @param string $foto1
     * @param string $foto2
     * @param string $foto3
     * @param string $foto4
     * @param int $marcas
     * @param int $modelos
     * @param int $estados
     * @param int $valor
     * @param string $peritaje
     * @param int $empresa
     * @param int $disponibilidad
     * @param string $caja
     * @param string $version
     * @param string $linea
     * @param string $soat
     * @param string $tecnicomencanica
     * 
     * @return array error o succes
     */
    public function createVehiculo( string $placa, 
                                    int $kilometro, 
                                    string $foto1, 
                                    string $foto2, 
                                    string $foto3, 
                                    string $foto4, 
                                    int $marcas, 
                                    int $modelos, 
                                    int $estados, 
                                    int $valor, 
                                    int $disponibilidad, 
                                    string $caja, 
                                    string $version, 
                                    string $linea, 
                                    string $soat, 
                                    string $tecnicomencanica,string $proveedor,int $precio_proveedor,
                                    string $combustible,
                                    float $cilindraje,
                                    float $facecolda,
                                    string $accesorios,
                                    string $llave,
                                    string $chasis,
                                    string $color,
                                    string $motor,
                                    string $matricula,
                                    string $tipo,
                                    string $servicio,
                                    string $serie,
                                    string $vin,
                                    String $carroseria

                                    ): array;

    /** Actualiza la informacion de un vehiculo
     * @param int $id_vehiculo
     * @param string $placa
     * @param int $kilometro
     * @param string $foto1
     * @param string $foto2
     * @param string $foto3
     * @param string $foto4
     * @param int $marcas
     * @param int $modelos
     * @param int $estados
     * @param int $valor
     * @param string $peritaje
     * @param int $empresa
     * @param int $disponibilidad
     * @param string $caja
     * @param string $version
     * @param string $linea
     * @param string $soat
     * @param string $tecnicomencanica
     * 
     * @return array
     */
    public function updateVehiculo( int $id_vehiculo, 
                                    string $placa, 
                                    int $kilometro, 
                                    int $marcas, 
                                    int $modelos, 
                                    int $estados, 
                                    int $valor, 
                                    int $disponibilidad, 
                                    string $caja, 
                                    string $version, 
                                    string $linea, 
                                    string $soat, 
                                    string $tecnicomencanica,string $proveedor,int $precio_proveedor,
                                    string $combustible,
                                    float $cilindraje,
                                    float $facecolda,
                                    string $accesorios,
                                    string $llave,
                                    string $chasis,
                                    string $color,
                                    string $motor,
                                    string $matricula,
                                    string $tipo,
                                    string $servicio,
                                    string $serie,
                                    string $vin,
                                    String $carroseria
                                    ): array;

    /** Elimina un vehiculo
     * @param int $id_vehiculo
     * 
     * @return array
     */
    public function deleteVehiculo(int $id_vehiculo): array;

    /** Consulta todos los vehiculos
     * @return array
     */
    public function allVehiculo(int $limite, int $offset ): array;

    /** consulta la informacion de un vehiculo
     * @param int $id_vehiculo
     * 
     * @return array
     */
    public function findVehiculo(int $id_vehiculo): array;

    /** Actualiza la foto
     * @param int $id_vehiculo
     * @param string $foto
     * @param string $key
     * 
     * @return [type]
     */
    public function updateVehiculoImage(int $id_vehiculo,string $foto,string $key):array;

    /** define como agotado
     * @param int $id_vehiculo
     * 
     * @return array
     */
    public function agotarVehiculo(int $id_vehiculo):array;

    /** retorna todos los vehiculos que esten en la lista de asociaciones
     * @return array
     */
    public function indexIntercompany():array;
}
