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
        "additional_info" => [
            "items" => [
                [
                    "id" => rand(1,100000).'cartmots',
                    "title" => "Cartmots mes",
                    "description" => "Pago de mensualidad de uso de la plataforma cartmots",
                    "picture_url" => "https://public.cartmots.com/storage/AUTOSSELECCIONADOS/logos//JNe29OBusTtrEKonazdFlIqQrgzsQwzjN4Tj5k6N.jpg",
                    "category_id" => "electronics",
                    "quantity" => 1,
                    "unit_price" => 350000,
                    "type" => "electronics",
                    "event_date" => Carbon::now(),
                    "warranty" => false,
                    "category_descriptor" => [
                        "passenger" => [],
                        "route" => [],
                    ],
                ],
            ],
            "payer" => [
                "first_name" => "bayron",
                "last_name" => "",
                "phone" => [
                    "area_code" => 57,
                    "number" => "3184482848",
                ]
            ],
        ],
        "application_fee" => null,
        "binary_mode" => false,
        "campaign_id" => null,
        "capture" => false,
        "coupon_amount" => null,
        "description" => "Payment for product",
        "differential_pricing_id" => null,
        "external_reference" => null,
        "installments" => 1,
        "metadata" => null,
        "payer" => [
            "entity_type" => "individual",
            "type" => "customer",
            "id" => null,
            "email" => "baironmenesesidarraga.990128@gmail.com",
            "identification" => [
                "type" => "CC",
                "number" => "1143994831",
            ],
        ],
        "payment_method_id" => $request['metodo'],
        "token" => "$uuid",
        "transaction_amount" => 350000,
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