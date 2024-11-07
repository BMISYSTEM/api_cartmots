<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class conversacionbot extends Model
{
    use HasFactory;
    protected $primaryKey = 'codigo_chat';
    protected $fillable = [
        'codigo_chat',
        'codigo_mensaje',
        'mensaje',
        'opcion1',
        'proximo1',
        'opcion2',
        'proximo2',
        'tipo',
    ];

}
