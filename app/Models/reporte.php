<?php

namespace App\Models;

use App\Http\Requests\users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reporte extends Model
{
    use HasFactory;

    protected $fillable = ["nombre", "secciones", "empresas", "tipo"];
}
