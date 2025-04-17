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
        $endpoint = "/online/link/v1";
        $key = $this->llavePriv;
        $currentNanoseconds = microtime(true) * 1e9; // Convertir microsegundos a nanosegundos
        $tenMinutesInNanoseconds = 10 * 60 * 1e9; // 10 minutos en nanosegundos
        $futureNanoseconds = $currentNanoseconds + $tenMinutesInNanoseconds;
        $array = [
            "amount_type"=>"CLOSE",
            "amount" => [
                "currency" => "COP",
                "tip_amount" => 0,
                "total_amount" => 5000
            ],
            "expiration_date"=>$futureNanoseconds,
            "payment_method" => ["POS"],
            "description"=>"Pago de soporte CRM cartmots",
            "callback_url"=>"https://cartmots.com/panel/dashboard",
            "payer_email"=>"baironmenesesidarraga.990128@gmail.com",

        ];
        $datos = [
                "amount_type" => "CLOSE",
                "amount"=> [
                 "currency"=>"COP",
                 "taxes"=> [
                   [
                     "type"=> "VAT",
                     "base"=>8403,
                     "value"=> 1597
                 ]
                 ],
                 "tip_amount"=> 0,
                 "total_amount"=> 10000
                ],
               "description"=> "Mi descripción del producto o servicio",
               "expiration_date"=>1798598454489,
               "payment_methods"=> [
                      "PSE"
                ],
               "image_url"=> "https://robohash.org/sad.png"
            ];
        $data = Http::withHeaders([
            'Authorization'=>"x-api-key $key",
            'Accept' => 'application/json',
        ])->post($this->baseUrl.$endpoint,$array);
        return response()->json($data->json());
    }
}