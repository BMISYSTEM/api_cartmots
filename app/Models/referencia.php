<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class referencia extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientes',
        'personal1_nombre',
        'personal1_ciudad',
        'personal1_telefono',
        'personal1_direccion',
        'personal2_nombre',
        'personal2_ciudad',
        'personal2_telefono',
        'personal2_direccion',
        'familiares1_nombre',
        'familiares1_ciudad',
        'familiares1_telefono',
        'familiares1_direccion',
        'familiares2_nombre',
        'familiares2_ciudad',
        'familiares2_telefono',
        'familiares2_direccion',
        'empresariales1_nombre',
        'empresariales1_ciudad',
        'empresariales1_nit',
        'empresariales1_direccion',
        'empresariales1_telefono',
        'empresariales2_nombre',
        'empresariales2_ciudad',
        'empresariales2_nit',
        'empresariales2_direccion',
        'empresariales2_telefono',
        'conyuge_nombre',
        'conyuge_fechanacimiento',
        'conyuge_cedula',
        'conyuge_empresa',
        'conyuge_telefono',
        'coyuge_salario',
        'conyuge_otros_ingreso',
        'conyuge_egresos',
        'pdf'
        ,'empresas'

        
    ];
}
