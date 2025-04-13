<?php

namespace App\Http\Controllers\Paypal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaypalController extends Controller
{
    // ✅ Token de acceso de Mercado Pago (usa el de producción en entorno real)
    protected $accessToken = "TEST-1085150894423410-030803-ea4be12ca2083d3a93f496874831507f-507411332";
    protected $baseUrl = "https://api.mercadopago.com/v1";

    /**
     * Obtener métodos de pago habilitados en Mercado Pago
     */
    public function metodosPago()
    {
        $response = Http::withToken($this->accessToken)
            ->acceptJson()
            ->get("{$this->baseUrl}/payment_methods");

        return response()->json($response->json(), $response->status());
    }

    /**
     * Crear un pago con tarjeta (tokenizado desde el frontend)
     */
    public function createPago(Request $request)
    {
        $request->validate([
            'token' => 'nullable|string',
            'payment_method_id' => 'nullable|string',
            'transaction_amount' => 'required|numeric|min:1',
            'payer_email' => 'nullable|email',
            'installments' => 'nullable|integer|min:1',
        ]);

        $uuid = (string) Str::uuid(); // Previene pagos duplicados

        $data = [
            "transaction_amount" => $request->transaction_amount,
            "token" => $request->token, // Asegúrate de que el token se esté enviando correctamente desde el frontend
            "description" => $request->description ?? "Pago desde la plataforma",
            "installments" => $request->installments ?? 1,
            "payment_method_id" => $request->payment_method_id, // Verifica que sea un método de pago válido
            "payer" => [
                "email" => $request->payer_email, // Asegúrate de que el correo electrónico sea válido
            ],
        ];
        
        $response = Http::withHeaders([
            "Authorization" => "Bearer {$this->accessToken}",
            "Accept" => "application/json",
            "X-Idempotency-Key" => $uuid,
        ])->post("{$this->baseUrl}/payments", $data);

        
        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'payment' => $response->json()
            ], 200);
        }

        // Log error para depuración
        Log::error('Mercado Pago Error', [
            'response' => $response->body()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Error al procesar el pago',
            'details' => $response->json()
        ], $response->status());
    }
}
