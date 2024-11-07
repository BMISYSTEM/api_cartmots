<?php 
namespace App\Http\Controllers\Motivos\Implement;

use App\Http\Controllers\Motivos\Interface\MotivosInterface;
use App\Models\motivo;
use Illuminate\Support\Facades\Auth;

class MotivosImplement implements MotivosInterface
{
    function createMotivo($motivo): array
    {
        try {
            $motivos = motivo::create(
                [
                    'nombre'=>$motivo,
                    'empresas'=>Auth::user()->empresas
                ]
                );
            return ['succes'=>'Se creo de forma correcta'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function updateMotivo($id, $motivo): array
    {
        try {
            $motivos = motivo::find($id);
            $motivos->nombre = $motivo;
            $motivos->save();
            return ['succes'=>'Se edito de forma correcta'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function deleteMotivo($id): array
    {
        try {
            $motivos = motivo::find($id)->delete();
            return ['succes'=>'Se elimino de forma correcta'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function findMotivo($id): array
    {
        try {
            $motivos = motivo::where('id',$id)->where('empresas',Auth::user()->empresas)->first();
            return ['succes'=>$motivos];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function allMotivo(): array
    {
        try {
            $motivos = motivo::where('empresas',Auth::user()->empresas)->get();
            return ['succes'=>$motivos];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
}