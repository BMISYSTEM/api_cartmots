<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class datos_credito extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientes',
        'marca',
        'vehiculo',
        'valor_seguro',
        'plan',
        'valor_vehiculo',
        'valor_cuota_extra',
        'valor_financiar',
        'tipo',
        'cuota_inicial',
        'plazo',
        'tasa',
        'financiera',
        'pdf'
        ,'empresas'
        
    ];
}
