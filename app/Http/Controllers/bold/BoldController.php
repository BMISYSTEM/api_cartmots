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
    function terminales(){
        $endpoint = "/payments/binded-terminals";
        $key = $this->llavePriv;
        $data = Http::withHeaders([
            'Authorization'=>"x-api-key $key",
            'Accept' => 'application/json',
        ])->get($this->baseUrl.$endpoint);
        return response()->json($data->json());
    }
    function createLinkPago(){
        $endpoint = "/payments/app-checkout";
        $key = $this->llavePriv;
        $data = [
            "amount" => [
                "currency" => "COP",
                "taxes" => [
                    [
                        "type" => "VAT",
                        "base" => 10000,
                        "value" => 1000
                    ]
                ],
                "tip_amount" => 0,
                "total_amount" => 1230000
            ],
            "payment_method" => "POS",
            "terminal_model" => "N86",
            "terminal_serial" => "N860W000000",
            "reference" => "d9b10690-981d-494d-bcb0-66a1dacab51d",
            "user_email" => "vendedor@comercio.com",
            "description" => "Compra de Prueba",
            "payer" => [
                "email" => "pagador@hotmail.com",
                "phone_number" => "3100000000",
                "document" => [
                    "document_type" => "CEDULA",
                    "document_number" => "1010140000"
                ]
            ]
        ];
        
        $data = Http::withHeaders([
            'Authorization'=>"x-api-key $key",
            'Accept' => 'application/json',
        ])->post($this->baseUrl.$endpoint,[

        ]);
        return response()->json($data->json());
    }
}