<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reporte_fuente_dato extends Model
{
    protected $fillable = [
        'nombre',
        'consulta',
        'empresas', 
        'descripcion'            
    ];
    use HasFactory;
}
