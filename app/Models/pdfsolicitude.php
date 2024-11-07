<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pdfsolicitude extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientes',
        'nombre',
        'users'
        ,'empresas'
        
    ];
}
