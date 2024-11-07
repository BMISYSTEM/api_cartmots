<?php

namespace App\Http\Controllers;

use App\Models\resultado;
use Illuminate\Http\Request;

class ResultadoController extends Controller
{
    public function create(Request $request)
    {
        return "esta creado un resultado...";
    }
    public function index()
    {
        return response()->json(resultado::all());
    }
}
