<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class negocio extends Model
{
    use HasFactory;

    protected $fillable =[
        'vehiculo' ,
        'valorventa',
        'porcentajedescuento', 
        'placaretoma' ,
        'valorretoma' ,
        'finalizado' ,
        'cliente'  ,
        'empresas' ,   
        'metodopago',
        'asesor',
        'vcredito',
        'vcuotaInicial',
        'vseparacion',
        'vtraspaso',
        'asesorios',
        'obsequios',
    ];
}
