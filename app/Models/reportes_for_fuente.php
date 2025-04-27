<?php

namespace App\Models;

use App\Http\Requests\users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reportes_for_fuente extends Model
{
    use HasFactory;

    protected $fillable = [
        'reportes',
        'fuente',
        'empresas'
    ];
}
