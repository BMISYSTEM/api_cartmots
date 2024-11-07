<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pdfasesorios extends Model
{
    use HasFactory;

    protected $fillable = [
        'setpdf',
        'nombre',
        'marca',
        'estado',
        'valor',
        'empresas'
    ];
}
