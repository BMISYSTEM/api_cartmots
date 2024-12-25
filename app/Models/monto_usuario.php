<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class monto_usuario extends Model
{
    use HasFactory;
    protected $fillable=[
        'valor',
        'usuario_id',
        'empresas'
    ];

}
