<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class config_chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'telefono',
        'id_telefono',
        'token_permanente',
        'empresas',
        'id_users'

    ];
}
