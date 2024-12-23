<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class logistica extends Model
{
    use HasFactory;

    protected $fillable = [
        'placa',
        'actividads',
        'motivos',
        'fecha',
        'valor',
        'finalizado',
        'empresas',
        'tipo_movimiento',
        'cargar_cuenta',
        'comentario',
        'soporte',
        'usuario'
    ];
}
