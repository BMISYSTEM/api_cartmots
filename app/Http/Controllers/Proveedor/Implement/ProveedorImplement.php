<?php
namespace App\Http\Controllers\Proveedor\Implement;

use App\Http\Controllers\Proveedor\Interface\ProveedorInterface;
use App\Models\proveedor;
use Illuminate\Support\Facades\Auth;

class ProveedorImplement implements ProveedorInterface
{
    function createProveedor($cedula,$nombre, $apellido, $telefono, $telefono2, $email, $direccion): array
    {
        try {
            $proveedor = proveedor::create(
                [
                    'nombre'=>$nombre,
                    'apellidos'=>$apellido,
                    'cedula'=>$cedula,
                    'direccion'=>$direccion,
                    'telefono'=>$telefono,
                    'telefono2'=>$telefono2,
                    'email'=>$email,
                    'empresas'=>Auth::user()->empresas
                ]
            );
            return ['succes'=>'Proveedor creado con exito'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function updateProveedor($id_proveedor,$cedula, $nombre, $apellido, $telefono, $telefono2, $email, $direccion): array
    {
        try {
            $proveedor = proveedor::find($id_proveedor);
            $proveedor->nombre = $nombre;
            $proveedor->apellidos = $apellido;
            $proveedor->cedula = $cedula;
            $proveedor->telefono = $telefono;
            $proveedor->telefono2 = $telefono2;
            $proveedor->email = $email;
            $proveedor->direccion = $direccion;
            $proveedor->save();
            return ['succes'=>'Proveedor actualizado con exito'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function deleteProveedor($id_proveedor): array
    {
        try {
            $proveedor = proveedor::find($id_proveedor)->delete();
            return ['succes'=>'Proveedor actualizado con exito'];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function findProveedor($id_proveedor): array
    {
        try {
            $proveedor = proveedor::find($id_proveedor);
            return ['succes'=>$proveedor];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
    function allProveedor(): array
    {
        try {
            $proveedor = proveedor::where('empresas',Auth::user()->empresas)->get();
            return ['succes'=>$proveedor];
        } catch (\Throwable $th) {
            return ['error'=>'Error inesperado en el servidor error:'.$th];
        }
    }
}