<?php

namespace App\Http\Controllers;

use Dotenv\Store\File\Paths;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\String\ByteString;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ImagenesController extends Controller
{
    public function store()
    {
        // $foto = $_GET['nombre'];
        $empresa = Auth::user()->empresas;
        $logoEmpresa = DB::select("select logo,nombre from empresas where id = ".$empresa);
        // $nombreImagen = explode("/",$logoEmpresa[0]->logo);
        $archivo = Storage::get(str_replace("storage","public",$logoEmpresa[0]->logo));
        // $archivo = Storage::get($foto);
      return $archivo;
      
    }

    public function link()
    {
        Artisan::call('storage:link');
    }
}
