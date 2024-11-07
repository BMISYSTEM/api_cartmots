<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pdfretoma extends Model
{
    use HasFactory;

    protected $fillable = [
        'setpdf',
        'placa',
        'marca',
        'refvehiculo',
        'modelo',
        'kilometraje',
        'valorcomercial',
        'descripcion'
        ,'empresas'
    ];
}
