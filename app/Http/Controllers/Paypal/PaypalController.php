<?php

namespace App\Http\Controllers\Paypal;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class PaypalController extends Controller
{
    public $apiKey = "TEST-a194d62e-4b7a-444a-94cd-ce3b2be7e791";
    public $baseUrl = "https://api.mercadopago.com/v1";

    function metodosPago()
    {
        $key = $this->apiKey;
        $respuesta = Http::withHeader([
            'Content-Type'=> 'application/json',
            'Authorization'=>"Bearer $key"
        ])->get($this->baseUrl.'/payment_methods');

        return response()->json($respuesta->json());
    }
}