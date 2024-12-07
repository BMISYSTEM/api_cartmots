<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messages_chat extends Model
{
    use HasFactory;
    protected $fillable = [
        'telefono',
        'message',
        'timestamp_message',
        'id_telefono',
        'send',
        'empresas'
    ];
}
