<?php

namespace App\Http\Controllers\bold;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BoldController extends Controller {
    public $llavePriv = "V4T6UjZX3zoC26JfQOL0jiMOxDBBPRW-QhsFwMhTh-A";
    public $baseUrl = "https://integrations.api.bold.co";
    /* consulta los metodos de pago  */
    function metodosPago(Request $request){
        $key = $this->llavePriv;
        $data = Http::withHeaders([
            'Autorization'=>"x-api-key $key",
            'Accept' => 'application/json',
        ])->get($this->baseUrl.'/payments/payment-methods');
        return response()->json($data->json());
    }
}