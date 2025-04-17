<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class factura extends Model
{
    use HasFactory;
    protected $fillable = [
        'descipcion',
        'estado',
        'fecha_limite',
        'idLink',
        'valor'
    ];
    
}
