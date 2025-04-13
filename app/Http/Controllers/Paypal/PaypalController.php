<?php

namespace App\Http\Controllers\Paypal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class PaypalController extends Controller
{
    public $apiKey = "TEST-1085150894423410-030803-ea4be12ca2083d3a93f496874831507f-507411332";
    public $baseUrl = "https://api.mercadopago.com/v1";

    function metodosPago()
    {
        $key = $this->apiKey;
        $respuesta = Http::withHeaders([
            'Authorization'=>"Bearer $key",
            'Accept' => 'application/json',
        ])->get($this->baseUrl.'/payment_methods');

        return response()->json($respuesta->json());
    }
}