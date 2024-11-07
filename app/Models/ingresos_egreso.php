<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ingresos_egreso extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientes',
        'ingresos_fijos',
        'gastos',
        'total_ingresos',
        'ingreso_variable',
        'otros_egresos',
        'total_activos',
        'otros_ingresos',
        'tiene_vehiculo',
        'total_pasivos',
        'pdf',
        'empresas'
        
    ];
}
