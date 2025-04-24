<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cliente extends Model
{
    use HasFactory;


    protected $fillable = [
        'nombre',
        'apellido',
        'cedula',
        'date',
        'telefono',
        'email',
        'estados',
        'users_id',
        'TIPO_DOCUMENTO',
        'FECHA_EXPEDICCION',
        'ciudad',
        'genero',
        'estado_civil',
        'direccion',
        'celular',
        'tipo_vivienda',
        'antiguedad_vivienda',
        'empresas',
        'origen',
        'direccion'
    ];


    public function user()
    {
        return $this->belongsTo(User::class,'users_id');
    }
    public function estado()
    {
        return $this->belongsTo(estado::class,'estados');
    }
    public function vehiculo()
    {
        return $this->belongsTo(vehiculo::class,'vehiculos');
    }
}
