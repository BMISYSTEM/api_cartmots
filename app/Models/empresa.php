<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class empresa extends Model
{
    protected $fillable = [
        'nombre',
        'usuarios',
        'registrados',
        'token',
        'confirmado'
    ];
    use HasFactory;
}
