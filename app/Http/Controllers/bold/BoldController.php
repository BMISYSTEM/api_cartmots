<?php

namespace App\Http\Controllers\bold;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BoldController extends Controller {
    public $llavePriv = "RpYgEC4H3YxdigNF-sfcP0rLmtJ1I04Frv3XmPEEIUc";
    public $baseUrl = "https://integrations.api.bold.co";
    /* consulta los metodos de pago  */
    function metodosPago(Request $request){
        $key = $this->llavePriv;
        $data = Http::withHeaders([
            'Authorization'=>"x-api-key $key",
            'Accept' => 'application/json',
        ])->get($this->baseUrl.'/payments/payment-methods');
        return response()->json($data->json());
    }
}