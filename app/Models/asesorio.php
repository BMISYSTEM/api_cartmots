<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class asesorio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'marcas',
        'estados',
        'valor',
        'descripcion',
        'foto1',
        'foto2',
        'foto3',
        'empresas'
    ];
}
