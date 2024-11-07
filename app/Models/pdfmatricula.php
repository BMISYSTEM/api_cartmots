<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pdfmatricula extends Model
{
    use HasFactory;

    protected $fillable=[
        'setpdf',
        'traspasos',
        'honorarios',
        'impuestos',
        'pignoracion',
        'certificadotradiccion',
        'siginperitaje'
        ,'empresas'
    ];
}
