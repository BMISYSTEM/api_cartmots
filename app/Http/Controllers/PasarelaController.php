<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Net\MPSearchRequest;

class PasarelaController extends Controller
{
    public function preferences(Request $request)
    {
        MercadoPagoConfig::setAccessToken('TEST-1085150894423410-030803-ea4be12ca2083d3a93f496874831507f-507411332');
        $cliente = new PreferenceClient();
        $preferencia = $cliente->create(
            [
                "items" => array(
                    array(
                        "title" => "Plan Mensual Normal",
                        "quantity" => 1,
                        "unit_price" => 250000,
                    )
                )
            ]
        );
        $preferencia->back_urls = array(
            "success" => "http://cartmots/panel/facturas",
            "failure" => "http://cartmots/panel/facturas",
            "pending" => "http://cartmots/panel/facturas"
        );
        $preferencia->redirect_urls = array(
            "success" => "http://cartmots/panel/facturas",
            "failure" => "http://cartmots/panel/facturas",
            "pending" => "http://cartmots/panel/facturas"
        );
        $preferencia->auto_return = "approved";
        $preferencia->notification_url = 'https://cbrcs7xp-8000.use.devtunnels.ms/api/notificacionpago';
        $preferencia->external_reference = $preferencia->id;
        return response()->json(['succes' => $preferencia]);
    }

    public function notificacionPago(Request $request)
    {

        MercadoPagoConfig::setAccessToken("TEST-1085150894423410-030803-ea4be12ca2083d3a93f496874831507f-507411332");
        $searchRequest = new MPSearchRequest(30, 0, [
            "sort" => "date_created",
            "criteria" => "desc",
            "external_reference" => "507411332-acb69ce9-d824-4709-b2fd-701a917d6805",
            "range" => "date_created",
            "begin_date" => "NOW-30DAYS",
            "end_date" => "NOW",
        ]);
        $client = new PaymentClient();
        $client->search($searchRequest);
        return response()->json($client);
    }
}
