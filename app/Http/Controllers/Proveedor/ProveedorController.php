<?php

namespace App\Http\Controllers\Proveedor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Proveedor\Implement\ProveedorImplement;
use App\Http\Controllers\Vehiculos\Controller\VehiculoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProveedorController extends Controller
{
    private $proveedor;
    protected $vehiculo;
    function __construct(ProveedorImplement $implement,VehiculoController $implementVehiculo)
    {
        $this->proveedor = $implement;
        $this->vehiculo = $implementVehiculo;
    }
    function createProveedor(Request $request): object
    {
        // validar
        $request = $request->validate(
            [
                'nombre' => 'required',
                'apellido' => 'required',
                'cedula' => 'required',
                'telefono' => 'required',
                'telefono2' => 'required',
                'email' => 'required',
                'direccion' => 'required',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio',
                'cedula.required' => 'La cedula es obligatorio',
                'apellido.required' => 'El apellido es obligatorio',
                'telefono.required' => 'El telefono es obligatorio',
                'telefono2.required' => 'El telefono de respaldo es obligatorio',
                'email.required' => 'El email es obligatorio',
                'direccion.required' => 'La direccion es obligatoria'
            ]
        );
        // implement
        $estatus = $this->proveedor->createProveedor($request['cedula'],$request['nombre'], $request['apellido'], $request['telefono'], $request['telefono2'], $request['email'], $request['direccion']);
        // crea el vehiculo 
        
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    function updateProveedor(Request $request): object
    {
        // validar
        $request = $request->validate(
            [
                'id_proveedor' => 'required|exists:proveedors,id,empresas,'. Auth::user()->empresas,
                'nombre' => 'required',
                'cedula' => 'required',
                'apellido' => 'required',
                'telefono' => 'required',
                'telefono2' => 'required',
                'email' => 'required',
                'direccion' => 'required',
            ],
            [
                'id_proveedor.required' => 'El id del proveedor es obligatorio',
                'nombre.required' => 'El nombre es obligatorio',
                'cedula.required' => 'La cedula es obligatorio',
                'apellido.required' => 'El apellido es obligatorio',
                'telefono.required' => 'El telefono es obligatorio',
                'telefono2.required' => 'El telefono de respaldo es obligatorio',
                'email.required' => 'El email es obligatorio',
                'direccion.required' => 'La direccion es obligatoria'
            ]
        );
        // implement
        $estatus = $this->proveedor->updateProveedor($request['id_proveedor'],$request['cedula'],$request['nombre'], $request['apellido'], $request['telefono'], $request['telefono2'], $request['email'], $request['direccion']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    function deleteProveedor(Request $request): object
    {
        // validar
        $request = $request->validate(
            [
                'id_proveedor' => 'required|exists:proveedors,id',
            ],
            [
                'id_proveedor.required' => 'El id del proveedor es obligatorio',
            ]
        );
        // implement
        $estatus = $this->proveedor->deleteProveedor($request['id_proveedor']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    function findProveedor(Request $request): object
    {
        // validar
        $request = $request->validate(
            [
                'id_proveedor' => 'required|exists:proveedors,id',
            ],
            [
                'id_proveedor.required' => 'El id del proveedor es obligatorio',
            ]
        );
        // implement
        $estatus = $this->proveedor->findProveedor($request['id_proveedor']);
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
    function allProveedor(): object
    {

        // implement
        $estatus = $this->proveedor->allProveedor();
        // respuesta
        return response()->json($estatus, array_key_exists('error', $estatus) ? 500 : 200);
    }
}
