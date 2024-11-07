<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class clientes_documento extends Model
{
    protected $fillable = [
        'usuario',
        'cliente',
        'empresa',
        'urldoc',
        'tipo', 
        'centrofinanciero'              
    ];
    use HasFactory;
}
