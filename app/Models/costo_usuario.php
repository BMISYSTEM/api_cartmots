<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class costo_usuario extends Model
{
    use HasFactory;

    protected $fillable = [
        'valor',
        'id_user',
        'empresas',
    ];
}
