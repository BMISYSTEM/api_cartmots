<?php

namespace App\Http\Controllers\Paypal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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

    function createPago(Request $request)
    {   
        $uuid = Str::uuid();
        
        $data = [
        "installments"=>1,
        "payer"=>[
            "email"=>"baironmenesesidarraga.990128@gmail.com",

        ],
        "token"=>$request['token'],
        "transaction_amount"=>350000,
    ];
    
        $key = $this->apiKey;
        $respuesta = Http::withHeaders([
            'Authorization'=>"Bearer $key",
            'Accept' => 'application/json',
            'X-Idempotency-Key'=>"$uuid"
        ])->post($this->baseUrl.'/payments',$data);

        return response()->json($respuesta->json());
    }
}