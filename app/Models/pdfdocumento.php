<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pdfdocumento extends Model
{
    use HasFactory;

    protected $fillable = [	
        'setpdf',
        'cedula',
        'solicitudcredito',
        'certificadolaboral',
        'extratos',
        'declaracion',
        'cartascomerciales',
        'facturaproveedor',
        'cartacupo',
        'camaraycomercio',
        'rut',
        'resolucionpension',
        'desprendibles',
        'certificadotradiccion',
        'empresas'
    ];
}
