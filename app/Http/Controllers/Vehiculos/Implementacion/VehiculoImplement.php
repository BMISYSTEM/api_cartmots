<?php
namespace App\Http\Controllers\Vehiculos\Implementacion;

use App\Http\Controllers\Vehiculos\Interfaces\VehiculoInterface;
use App\Models\empresa;
use App\Models\vehiculo;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class VehiculoImplement implements VehiculoInterface
{
    /** Crea un vehiculo con toda la informacion de la base de datos 
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
    function createVehiculo(    string $placa, 
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
                                string $tecnicomencanica,
                                string $proveedor,
                                int $precio_proveedor, 
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
                                ): array
    {
        try {
            $empresa = Auth::user()->empresas;
            $nomempresa = empresa::find($empresa);
            $pathFoto1 = Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/vehiculos',$foto1));
            $pathFoto2 = Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/vehiculos',$foto2));
            $pathFoto3 = Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/vehiculos',$foto3));
            $pathFoto4= Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/vehiculos',$foto4));
            $vehiculo = vehiculo::create(
                [
                    'placa'=>$placa,
                    'kilometraje'=>$kilometro,
                    'foto1'=>$pathFoto1,
                    'foto2'=>$pathFoto2,
                    'foto3'=>$pathFoto3,
                    'foto4'=>$pathFoto4,
                    'marcas'=>$marcas,
                    'modelos'=>$modelos,
                    'estados'=>$estados,
                    'valor'=>$valor,
                    'empresas'=>$empresa,
                    'disponibilidad'=>$disponibilidad,
                    'caja'=>$caja,
                    'version'=>$version,
                    'linea'=>$linea,
                    'soat'=>$soat,
                    'tecnomecanica'=>$tecnicomencanica,
                    'peritaje'=>'',
                    'proveedor'=>$proveedor,
                    'precio_proveedor'=>$precio_proveedor,
                    'combustible'=>$combustible,
                    'cilindraje'=>$cilindraje,
                    'facecolda'=>$facecolda,
                    'accesorios'=>$accesorios,
                    'llave'=>$llave,
                    'chasis'=>$chasis,
                    'color'=>$color,
                    'motor'=>$motor,
                    'matricula'=>$matricula,
                    'tipo'=>$tipo,
                    'servicio'=>$servicio,
                    'serie'=>$serie,

                ]
            );
            return ['succes'=>'Se creo de forma exitosa el vehiculo'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    /** Actualiza toda la informacion de la base de datos de un vehiculo
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
    function updateVehiculo(    int $id_vehiculo, 
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
                                ): array
    {
        try {
            /**
             * Consulta solo el vehiculo
             */
            $vehiculo = vehiculo::find($id_vehiculo);
            $empresa = Auth::user()->empresa;
            $nomempresa = empresa::find($empresa);
            /**
             * update
             */
            $vehiculo->placa = $placa;
            $vehiculo->kilometraje = $kilometro;
            $vehiculo->marcas = $marcas;
            $vehiculo->modelos = $modelos;
            $vehiculo->estados = $estados;
            $vehiculo->valor = $valor;
            $vehiculo->disponibilidad = $disponibilidad;
            $vehiculo->caja = $caja;
            $vehiculo->version = $version;
            $vehiculo->linea = $linea;
            $vehiculo->soat = $soat;
            $vehiculo->tecnomecanica = $tecnicomencanica;
            $vehiculo->proveedor = $proveedor;
            $vehiculo->precio_proveedor = $precio_proveedor;
            $vehiculo->combustible = $combustible;
            $vehiculo->cilindraje = $cilindraje;
            $vehiculo->facecolda = $facecolda;
            $vehiculo->accesorios = $accesorios;
            $vehiculo->llave = $llave;
            $vehiculo->chasis = $chasis;
            $vehiculo->color = $color;
            $vehiculo->motor = $motor;
            $vehiculo->matricula = $matricula;
            $vehiculo->tipo = $tipo;
            $vehiculo->servicio = $servicio;
            $vehiculo->serie = $serie;
            

            /**
             * Se guardan los campos anteriores
             */
            $vehiculo->save();
            return ['succes'=>'Se actualizo de forma correcta'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    /** elimina un vehiuculo de la db
     * @param int $id_vehiculo
     * 
     * @return array
     */
    function deleteVehiculo(int $id_vehiculo): array
    {
        try {
            $vehiculo = vehiculo::find($id_vehiculo);
            /**
             * elimina archivos 
             */
            Storage::delete(str_replace('storage','public',$vehiculo->foto1));
            Storage::delete(str_replace('storage','public',$vehiculo->foto2));
            Storage::delete(str_replace('storage','public',$vehiculo->foto3));
            Storage::delete(str_replace('storage','public',$vehiculo->foto4));
            Storage::delete(str_replace('storage','public',$vehiculo->peritaje));
            /**
             * Elmina de la bd
             */
            $vehiculo->delete();
            return ['succes'=>'Se elimino de forma exitosa el vehiculo'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    /** consulta todos los vehiculos
     * @return array
     */
    function allVehiculo(int $limite,int $offsets): array
    {
        try {
            
            $limit = 15;
            $offset = 1;
            if($limit and $offsets){
                $limit = $limite;
                $offset = $offsets;
            }
            $empresa = Auth::user()->empresas;
            $count = vehiculo::where('empresas',$empresa)->get();
            $vehiculos = DB::select('
            SELECT 	v.id, 
                    v.placa, 
                    v.kilometraje, 
                    v.foto1, 
                    v.foto2, 
                    v.foto3, 
                    v.foto4, 
                    v.created_at, 
                    v.updated_at, 
                    v.marcas, 
                    v.modelos, 
                    v.estados, 
                    v.valor, 
                    v.peritaje, 
                    v.empresas, 
                    v.disponibilidad, 
                    v.caja, 
                    v.version, 
                    v.linea, 
                    v.soat, 
                    v.precio_proveedor,
                    v.tecnomecanica,
                    v.combustible,
                    v.cilindraje,
                    v.facecolda,
                    v.accesorios,
                    v.llave,
                    v.chasis,
                    v.color,
                    v.motor,
                    v.matricula,
                    v.tipo,
                    v.servicio,
                    v.serie,
                    mar.nombre as marca_nombre,
                    m.year as model_nombre,
                    e.estado as estado_nombre,
                    v.proveedor,
                    p.nombre as nombre_proveedor
            FROM vehiculos v
            inner join modelos m on v.modelos = m.id
            inner join estados e on v.estados = e.id
            inner join marcas mar on v.marcas = mar.id
            left join proveedors p on v.proveedor = p.id
            where v.empresas = '.$empresa.'  LIMIT 1000 OFFSET 0 ');
            return [['succes'=>$vehiculos,'count'=>count($count)]];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    /** consulta un solo vehiculo
     * @param int $id_vehiculo
     * 
     * @return array
     */
    function findVehiculo(int $id_vehiculo): array
    {
        try {
            $vehiculos = DB::select('
            SELECT 	v.id, 
                    v.placa, 
                    v.kilometraje, 
                    v.foto1, 
                    v.foto2, 
                    v.foto3, 
                    v.foto4, 
                    v.created_at, 
                    v.updated_at, 
                    v.marcas, 
                    v.modelos, 
                    v.estados, 
                    v.valor, 
                    v.peritaje, 
                    v.empresas, 
                    v.disponibilidad, 
                    v.caja, 
                    v.version, 
                    v.linea, 
                    v.soat, 
                    v.precio_proveedor,
                    v.tecnomecanica,
                    v.combustible,
                    v.cilindraje,
                    v.facecolda,
                    v.accesorios,
                    v.llave,
                    v.chasis,
                    v.color,
                    v.motor,
                    v.matricula,
                    v.tipo,
                    v.servicio,
                    v.serie,
                    mar.nombre as marca_nombre,
                    m.year as model_nombre,
                    e.estado as estado_nombre
            FROM vehiculos v
            inner join modelos m on v.modelos = m.id
            inner join estados e on v.estados = e.id
            inner join marcas mar on v.marcas = mar.id
            where v.empresas = 2  and v.id = '.$id_vehiculo.' 
             ');
            return ['succes'=>$vehiculos];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    function updateVehiculoImage(int $id_vehiculo, string $foto, string $key): array
    {
        try {
            $vehiculo = vehiculo::find($id_vehiculo);
            $empresa = Auth::user()->empresas;
            $nomempresa = empresa::find($empresa);
           
            
            switch ($key) {
                case 'foto1':
                    Storage::delete(str_replace('storage','public',$vehiculo->foto));
                    $pathFoto = Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/vehiculos',$foto));
                    $vehiculo->foto1 =  $pathFoto;
                    $vehiculo->save();
                    break;
                
                case 'foto2':
                    Storage::delete(str_replace('storage','public',$vehiculo->foto));
                    $pathFoto = Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/vehiculos',$foto));
                    $vehiculo->foto2 =  $pathFoto;
                    $vehiculo->save();
                    break;

                case 'foto3':
                    Storage::delete(str_replace('storage','public',$vehiculo->foto));
                    $pathFoto = Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/vehiculos',$foto));
                    $vehiculo->foto3 =  $pathFoto;
                    $vehiculo->save();
                    break;

                case 'foto4':
                    Storage::delete(str_replace('storage','public',$vehiculo->foto));
                    $pathFoto = Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/vehiculos',$foto));
                    $vehiculo->foto4 =  $pathFoto;
                    $vehiculo->save();
                    break;

                case 'peritaje':
                    Storage::delete(str_replace('storage','public',$vehiculo->foto));
                    $pathFoto = Storage::url(Storage::putFile('public/'.$nomempresa['nombre'].'/peritaje',$foto));
                    $vehiculo->peritaje =  $pathFoto;
                    $vehiculo->save();
                    break;
            }
            return ['succes'=>'Se actualizo con exito'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }

    public function agotarVehiculo(int $id_vehiculo): array
    {
        try {
            $vehiculo = vehiculo::find($id_vehiculo);
            if($vehiculo->disponibilidad === 1) 
            {
                $vehiculo->disponibilidad = 0;
            }else{
                
                $vehiculo->disponibilidad = 1;
            }
            $vehiculo->save();
            return ['succes'=>'Se actualizo con exito'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }
    function indexIntercompany(): array
    {
        try {
            $vehiculos = DB::select('
            select 
                v.id, 
                v.placa, 
                v.kilometraje, 
                v.foto1, 
                v.foto2, 
                v.foto3, 
                v.foto4, 
                v.created_at, 
                v.updated_at, 
                v.marcas,
                m.nombre as marca_nombre,
                v.modelos, 
                mo.year as model_nombre,
                v.estados,
                e.estado,
                v.valor, 
                v.peritaje, 
                v.empresas, 
                em.nombre nombre_empresa,
                em.telefono,
                v.disponibilidad, 
                v.caja, 
                v.version, 
                v.linea, 
                v.soat, 
                v.tecnomecanica from vehiculos v
                inner join marcas m on v.marcas = m.id
                inner join modelos mo on v.modelos = mo.id
                inner join estados e on v.estados = e.id
                inner join empresas em on v.empresas = em.id and em.activo = 1
                where v.disponibilidad = 1
            ');
            return ['succes'=>$vehiculos];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor '.$th];
        }
    }
}