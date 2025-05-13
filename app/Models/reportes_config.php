<?php

namespace App\Models;

use App\Http\Requests\users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reportes_config extends Model
{
    use HasFactory;

    protected $fillable = [
        'campo',
        'seleccion',
        'titulo',
        'color',
        'filtro',
        'posicion',
        'total',
        'reportes',
        'empresas',
        'condicion'
    ];
}
