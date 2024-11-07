<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pdffinanciero extends Model
{
    use HasFactory;

    protected $fillable = [
        'setpdf',
        'placa',
        'kilometraje',
        'valor',
        'valorfinanciar',
        'mesesmanual',
        'cuotaextra',
        'tasa',
        'cuarenta',
        'sesenta',
        'setenta',
        'ochenta',
        'seguro',
        'foto',
        'marca',
        'modelo'
        ,'empresas'
    ];
}
