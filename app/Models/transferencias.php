<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class transferencias extends Model
{
    protected $fillable = [
        'empresa_de',
        'empresa_para',
        'clientes',
        'usuario',
        'comentario',
    ];
    use HasFactory;
}
