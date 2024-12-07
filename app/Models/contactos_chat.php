<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contactos_chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'telefono',
        'nombre',
        'id_telefono',
        'empresas',
    ];
}
