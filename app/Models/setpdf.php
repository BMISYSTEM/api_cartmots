<?php

namespace App\Models;

use App\Http\Requests\users;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class setpdf extends Model
{
    use HasFactory;

    protected $fillable = [
        'clientes',
        'users',
        'pdf'
        ,'empresas'
    ];
}
