<?php
namespace App\Http\Controllers\Actividades\Implement;

use App\Http\Controllers\Actividades\Interface\ActividadesInterface;
use App\Models\actividad;
use Illuminate\Support\Facades\Auth;

class ActividadesImplement implements ActividadesInterface
{
    function createActividad($nombre): array
    {
        try {
            $actividad = actividad::create(
                [
                    'nombre'=>$nombre,
                    'empresas'=>Auth::user()->empresas
                ]
                );
            return ['succes' => 'Se creo correctamente la actividad '];
        } catch (\Throwable $th) {
            return ['error' => 'Error inesperado en el servidro '.$th];
        }
    }

    function updateActividad($id, $nombre): array
    {
        try {
            $actividad = actividad::find($id);
            $actividad->nombre = $nombre;
            $actividad->save();
            return ['succes' => 'Se actualizo correctamente la actividad '];
        } catch (\Throwable $th) {
            return ['error' => 'Error inesperado en el servidro '.$th];
        }
    }

    function deleteActividad($id): array
    {
        try {
            $actividad = actividad::find($id)->delete();
            return ['succes' => 'Se elimino correctamente la actividad '];
        } catch (\Throwable $th) {
            return ['error' => 'Error inesperado en el servidro '.$th];
        }
    }
    function findActividad($id): array
    {
        try {
            $actividad = actividad::where('id',$id)->where('empresas',Auth::user()->empresas)->first();
            return ['succes' => $actividad];
        } catch (\Throwable $th) {
            return ['error' => 'Error inesperado en el servidro '.$th];
        }
    }
    function allActividad(): array
    {
        try {
            $actividad = actividad::where('empresas',Auth::user()->empresas)->get();
            return ['succes' => $actividad ];
        } catch (\Throwable $th) {
            return ['error' => 'Error inesperado en el servidro '.$th];
        }
    }
}