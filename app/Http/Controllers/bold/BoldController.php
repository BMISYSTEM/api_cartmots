<?php

namespace App\Http\Controllers\bold;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BoldController extends Controller {
    public $llavePriv = "EsE5cPX72fXUxv7GnDbszA";
    public $baseUrl = "https://integrations.api.bold.co";
    /* consulta los metodos de pago  */
    function metodosPago(Request $request){
        $key = $this->llavePriv;
        $response = Http::withHeaders([
            'Autorization'=>"x-api-key $key",
            'Accept' => 'application/json',
        ])->get($this->baseUrl.'/payments/payment-methods');
        return response()->json($response);
    }
}