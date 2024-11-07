<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class proveedor extends Model
{
    use HasFactory;

    protected $fillable =[
        'nombre',
        'apellidos',
        'cedula',
        'direccion',
        'telefono',
        'telefono2',
        'email',
        'empresas'
    ];
}
