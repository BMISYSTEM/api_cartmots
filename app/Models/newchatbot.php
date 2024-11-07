<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class newchatbot extends Model
{
    use HasFactory;
    protected $primaryKey = 'codigo';
    
    protected $fillable =[
        'codigo',
        'nombre',
        'descripcion',
        'inicio',
        'fin',
        'empresa',
    ];
}
