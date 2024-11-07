<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class solicitud_asociacione extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_solicitante',
        'empresa_receptora',
        'vehiculo',
        'clientes',
    ];
}
