<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class informacion_laborale extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientes',
        'contrato',
        'empresa',
        'direccion',
        'nit',
        'profecion',
        'telefono',
        'antiguedad',
        'pdf'
        ,'empresas'
        
    ];
}
