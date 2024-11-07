<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class numberchatbot extends Model
{
    use HasFactory;
    protected $primaryKey = 'codigo_chat';
    protected $fillable = [
        'telefono',
        'codigo_campana',
        'estado',
        'codigo_chat'
    ];
}
