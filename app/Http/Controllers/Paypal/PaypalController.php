<?php

namespace App\Http\Controllers\Paypal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    function createPago(Request $request)
    {   
        
        $data = [
        "additional_info" => [
            "items" => [
                [
                    "id" => "MLB2907679857",
                    "title" => "Point Mini",
                    "description" => "Point product for card payments via Bluetooth.",
                    "picture_url" => "https://public.cartmots.com/storage/AUTOSSELECCIONADOS/logos//JNe29OBusTtrEKonazdFlIqQrgzsQwzjN4Tj5k6N.jpg",
                    "category_id" => "electronics",
                    "quantity" => 1,
                    "unit_price" => 58,
                    "type" => "electronics",
                    "event_date" => "2023-12-31T09:37:52.000-04:00",
                    "warranty" => false,
                    "category_descriptor" => [
                        "passenger" => [],
                        "route" => [],
                    ],
                ],
            ],
            "payer" => [
                "first_name" => "Test",
                "last_name" => "Test",
                "phone" => [
                    "area_code" => 11,
                    "number" => "987654321",
                ],
                "address" => [
                    "zip_code" => "12312-123",
                    "street_name" => "Av das Nacoes Unidas",
                    "street_number" => 3003
                ],
            ],
            "shipments" => [
                "receiver_address" => [
                    "zip_code" => "12312-123",
                    "state_name" => "Rio de Janeiro",
                    "city_name" => "Buzios",
                    "street_name" => "Av das Nacoes Unidas",
                    "street_number" => 3003,
                ],
                "width" => null,
                "height" => null,
            ],
        ],
        "application_fee" => null,
        "binary_mode" => false,
        "campaign_id" => null,
        "capture" => false,
        "coupon_amount" => null,
        "description" => "Payment for product",
        "differential_pricing_id" => null,
        "external_reference" => "MP0001",
        "installments" => 1,
        "metadata" => null,
        "payer" => [
            "entity_type" => "individual",
            "type" => "customer",
            "id" => null,
            "email" => "test_user_123@testuser.com",
            "identification" => [
                "type" => "CPF",
                "number" => "95749019047",
            ],
        ],
        "payment_method_id" => $request['metodo'],
        "token" => "ff8080814c11e237014c1ff593b57b4d",
        "transaction_amount" => 58000,
        "currency_id" => "COP",
    ];
    
        $key = $this->apiKey;
        $respuesta = Http::withHeaders([
            'Authorization'=>"Bearer $key",
            'Accept' => 'application/json',
        ])->post($this->baseUrl.'/payments',$data);

        return response()->json($respuesta->json());
    }
}