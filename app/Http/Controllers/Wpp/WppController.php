<?php

namespace App\Http\Controllers\Wpp;

use App\Http\Controllers\Controller;
use App\Models\config_chat;
use App\Models\contactos_chat;
use App\Models\messages_chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WppController extends Controller
{
    const token = "WPPAPLICATION";
    const webhook_url = "https://public.cartmots.com/api/wpp";

    function verificarToken(Request $req)
    {
        try {
            $tokenapp = 'WPPAPLICATION';
            $token = $req['hub_verify_token'];
            $challenge = $req['hub_chanllenge'];

            if (isset($challenge) && isset($token) && $token === $tokenapp) {
                return response()->json($challenge);
            } else {
                return response()->json(['error' => 'error'], 400);
            }
        } catch (\Throwable $th) {
            return response()->json(['error' => $th], 400);
        }
    }
    /**post */
    function wppPost(Request $req)
    {
        $filePath = storage_path('./seguimiento.txt');
        $requestData = $req->all();
        $jsonData = json_encode($requestData);
        file_put_contents($filePath, $jsonData, FILE_APPEND);
        try {
            $comentario = '';
            $from = 0;
            if (
                isset($req['entry'][0]['changes'][0]['value']['messages'][0]['from'])
                && isset($req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])
                && ($from = $req['entry'][0]['changes'][0]['value']['messages'][0]['from'])
                && ($comentario = $req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])
            ) {
                /* guardar el contacto  */
                $telefono = $req['entry'][0]['changes'][0]['value']['messages'][0]['from'] ?? '0';
                $nombre = $req['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? 'sin nombre definido';
                $id_telefono = $req['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'] ?? '0';
                $message = $req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'] ?? null;

                // Obtener configuración del chat
                $config_chat = config_chat::where('id_telefono', $id_telefono)->first();
                $empresas = $config_chat ? $config_chat->empresas : null;

                if ($telefono && $message && $empresas) {
                    // Verificar si existe el contacto
                    $contacto = contactos_chat::where('telefono', $telefono)->where('empresas', $empresas)->get();
                    if ($contacto->isEmpty()) {
                        contactos_chat::create([
                            'telefono' => $telefono,
                            'nombre' => $nombre,
                            'id_telefono' => $id_telefono,
                            'empresas' => $empresas,
                            'id_users' => $config_chat->id_users,
                            'bot' => 0
                        ]);
                    }

                    // Crear mensaje
                    messages_chat::create([
                        'telefono' => $telefono,
                        'message' => $message,
                        'timestamp_message' => $req['entry'][0]['changes'][0]['value']['messages'][0]['timestamp'] ?? time(),
                        'id_telefono' => $id_telefono,
                        'send' => 0,
                        'empresas' => $empresas
                    ]);
                    $contactovalidation = contactos_chat::where('telefono', $telefono)->where('empresas', $empresas)->first();
                    if ($contacto->isEmpty()) {
                        $this->botMessage($comentario, $from, $id_telefono, 0);
                    } elseif ($contactovalidation->bot == 1) {
                        $this->botMessage($comentario, $from, $id_telefono, 1);
                    } else {
                        $this->botMessage($comentario, $from, $id_telefono, 0);
                    }
                }
                /**envia los mensajes **/
                /* $this->sendMessage($comentario, $from); */
                /* $this->sendMessage($comentario, $from,$id_telefono); */
            } else {
                
                if (
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['from']) &&
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['type']) &&
                    ($from = $req['entry'][0]['changes'][0]['value']['messages'][0]['from']) &&
                    ($type = $req['entry'][0]['changes'][0]['value']['messages'][0]['type']) &&
                    ($id_telefono = $req['entry'][0]['changes'][0]['value']['metadata']['display_phone_number'])
                ) {
                    if (
                        $type === 'interactive' &&
                        isset($req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['id']) &&
                        isset($req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title']) &&
                        ($buttonId = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['id']) &&
                        ($id_telefono = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['id']) &&
                        ($buttonTitle = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title'])
                    ) {
                        $this->botMessage($buttonId, $from, $id_telefono, 0);
                    } elseif (
                        $type === 'text' &&
                        isset($req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) &&
                        ($comentario = $req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])
                    ) {
                        // Es un mensaje de texto
                        return response()->json([
                            'status' => 'success',
                            'message' => "El usuario $from envió el mensaje: $comentario"
                        ]);
                    }
                }
                if (
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['from']) &&
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['id']) &&
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['title'])
                ) {
                    // 📌 Extraer información relevante
                    $from = $req['entry'][0]['changes'][0]['value']['messages'][0]['from']; // Número de teléfono del remitente
                    $name = $req['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? 'Desconocido'; // Nombre del usuario
                    $message_id = $req['entry'][0]['changes'][0]['value']['messages'][0]['id']; // ID del mensaje
                    $selected_option_id = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['id']; // ID de la opción elegida
                    $selected_option_title = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['title']; // Texto de la opción elegida
                    $receiver_number = $req['entry'][0]['changes'][0]['value']['metadata']['display_phone_number']; // Número que recibió el mensaje
                    $this->botMessage($selected_option_id, $from, $receiver_number, 0);
                    // 📝 Registrar en el log de Laravel
                    Log::info("📩 Mensaje recibido en WhatsApp:");
                    Log::info("🔹 Nombre: $name");
                    Log::info("📞 Número: $from");
                    Log::info("📥 ID del mensaje: $message_id");
                    Log::info("✅ Opción seleccionada: $selected_option_title (ID: $selected_option_id)");
                    Log::info("📲 Número receptor: $receiver_number");
                } else {
                    Log::warning("⚠️ No se encontró un mensaje válido en la solicitud.");
                }
            }

            file_put_contents($filePath, "--------message---------", FILE_APPEND);
        } catch (\Throwable $th) {
            file_put_contents($filePath, "Error generado--.$th", FILE_APPEND);
            return response()->json(['message' => 'EVENT_RECEIVED'], 200);
        }

        return response()->json(['message' => 'EVENT_RECEIVED'], 200);
    }

    function sendMessage(Request $request)
    {

        $filePath = storage_path('./seguimiento.txt');
        file_put_contents($filePath, "--------numero---------" . $request['numero'], FILE_APPEND);
        $curl = curl_init();

        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $request['numero'],
            "type" => "text",
            "text" => [
                "preview_url" => false,
                "body" => $request['message']
            ]
        ];
        /* } else if (strpos($comentario, "boton") !== false) {
            $data = [

                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "interactive",
                "interactive" => [
                    "type" => "button",
                    "body" => [
                        "text" => "prueba de botones"
                    ],
                    "action" => [
                        "buttons" => [
                            [
                                "type" => "reply",
                                "reply" => [
                                    "id" => "button1",
                                    "title" => "primera opcion"
                                ]
                            ],
                            [
                                "type" => "reply",
                                "reply" => [
                                    "id" => "button2",
                                    "title" => "segunda opcion"
                                ]
                            ]
                        ]
                    ]
                ]

            ];
        } else {
            $data = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $numero,
                "type" => "text",
                "text" => [
                    "preview_url" => false,
                    "body" => "no se logro leer el mensaje $comentario"
                ]
            ];
        } */


        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
            ),
        ));


        $response = curl_exec($curl);
        curl_close($curl);
        if ($response) {
            $empresas = Auth::user()->empresas;
            // Crear mensaje
            messages_chat::create([
                'telefono' => $request['numero'],
                'message' => $request['message'],
                'timestamp_message' => time(),
                'id_telefono' => $request['id_telefono'],
                'send' => 1,
                'empresas' => $empresas
            ]);
        }
    }
    function wppGet(Request $req)
    {
        $token = 'WPPAPLICATION';
        if (
            $req->has('hub_mode') &&
            $req->has('hub_verify_token') &&
            $req->has('hub_challenge') &&
            $req->query('hub_mode') === "subscribe" &&
            $req->query('hub_verify_token') === $token
        ) {
            return response($req->query('hub_challenge'));
        } else {
            return response()->json([], 403);
        }
    }

    function allContactos()
    {
        $empresa = Auth::user()->empresas;
        $id_user = Auth::user()->id;
        try {
            $contactos = contactos_chat::where('empresas', $empresa)->where('id_users', $id_user)->get();
            return response()->json(['succes' => $contactos]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error generado ' . $th], 500);
        }
    }

    function allMessages(Request $request)
    {
        $telefono = $request->query('telefono');
        $empresas = Auth::user()->empresas;
        try {
            $messages = messages_chat::where('telefono', $telefono)->where('empresas', $empresas)->get();
            return response()->json(['succes' => $messages]);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'error generado en el servidor' . $th], 500);
        }
    }
    function botMessage($comentario, $from, $id_telefono, $nuevo)
    {
        $respuesta = '';
        Log::info("Mensaje = ".$comentario);
        if ($nuevo == 1) {
            $curl2 = curl_init();
            $message = [
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $from,
                "type" => "interactive",
                "interactive" => [
                    "type" => "button",
                    "body" => [
                        "text" => "🔹 ¡Hola, buen día! ☀️\n👋 Mi nombre es Brandon Arbelaez, especialista en el sector financiero 💰 y automotriz 🚗.\n📌 Permíteme hacerte unas preguntas 📝 para poder asesorarte de la mejor manera.\n✨ ¡Estoy aquí para ayudarte!\nDeseas comprar vehiculo?"
                    ],
                    "action" => [
                        "buttons" => [
                            [
                                "type" => "reply",
                                "reply" => [
                                    "id" => "ford",
                                    "title" => "Nuevo FORD"
                                ]
                            ],
                            [
                                "type" => "reply",
                                "reply" => [
                                    "id" => "multimarca",
                                    "title" => "Usado Multimarca"
                                ]
                            ]
                        ]
                    ]
                ]
            ];
            curl_setopt_array($curl2, array(
                CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                ),
            ));
            $response = curl_exec($curl2);
            curl_close($curl2);
            $contacto = contactos_chat::where('telefono', $from)->first();
            $contacto->bot = 0;
            $contacto->save();
        } else {
            $contacto = contactos_chat::where('telefono', $from)->first();
            if($contacto->finalizado == 1){
                return;
            }
            if($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 0  && $contacto->kilometraje == 0  && $contacto->color == 0   && $contacto->precio_estimado == 0){
                $curl = curl_init();
                //mensaje de presentacion 
                $data = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => "Modelo:"
                    ]
                ];
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
        
        
                $response = curl_exec($curl);
                curl_close($curl);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->modelo = 1;
                $contacto->save();
            }elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 0  && $contacto->color == 0   && $contacto->precio_estimado == 0){
                $curl = curl_init();
                //mensaje de presentacion 
                $data = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => "Kilometraje:"
                    ]
                ];
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
        
        
                $response = curl_exec($curl);
                curl_close($curl);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->kilometraje = 1;
                $contacto->save();
            }
            elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 0   && $contacto->precio_estimado == 0){
                $curl = curl_init();
                //mensaje de presentacion 
                $data = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => "Color:"
                    ]
                ];
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
        
        
                $response = curl_exec($curl);
                curl_close($curl);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->color = 1;
                $contacto->save();
            }
            elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 0){
                $curl = curl_init();
                //mensaje de presentacion 
                $data = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => "Precio estimado:"
                    ]
                ];
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
        
        
                $response = curl_exec($curl);
                curl_close($curl);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->precio_estimado = 1;
                $contacto->save();
            }
            elseif($contacto->negocio == 0 &&  $contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1){
                $curl2 = curl_init();
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "interactive",
                    "interactive" => [
                        "type" => "button",
                        "body" => [
                            "text" => "Quisiera saber como deseas hacer el negocio "
                        ],
                        "action" => [
                            "buttons" => [
                                [
                                    "type" => "reply",
                                    "reply" => [
                                        "id" => "contado1",
                                        "title" => "De Contado"
                                    ]
                                ],
                                [
                                    "type" => "reply",
                                    "reply" => [
                                        "id" => "financiado1",
                                        "title" => "Financiado"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->negocio = 1;
                $contacto->save();
            }
            elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1 && stripos($comentario, "contado1") !== false ){
                $curl2 = curl_init();
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => "Genial hemos finalizado En unos minutos uno de nuestros Asesores te contactara para continuar el proceso, gracias..."
                    ]
                ];
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->finalizado = 1;
                $contacto->save();
            }
            elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1 && stripos($comentario, "financiado1") !== false ){
                $curl2 = curl_init();
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "interactive",
                    "interactive" => [
                        "type" => "button",
                        "body" => [
                            "text" => "Genial, te podemos ayudar con la financiación, voy hacerte unas preguntas y revisamos la viabilidad. "
                        ],
                        "action" => [
                            "buttons" => [
                                [
                                    "type" => "reply",
                                    "reply" => [
                                        "id" => "empleado1",
                                        "title" => "Soy empleado"
                                    ]
                                ],
                                [
                                    "type" => "reply",
                                    "reply" => [
                                        "id" => "independiente1",
                                        "title" => "Soy Independiente"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
            }
            elseif($contacto->negocio == 1 && $contacto->ingresos == 0 && $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1){
                $curl2 = curl_init();
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => "Cual es tu ingreso mensual ? "
                    ]
                ];
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->ingresos = 1;
                $contacto->save();
            }
            elseif($contacto->negocio == 1 && $contacto->ingresos == 1 && $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1){
                $curl2 = curl_init();
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => "SUPER !!! Hemos terminado, en unos momentos nos pondremos en contacto para continuar el proceso  "
                    ]
                ];
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->finalizado = 1;
                $contacto->save();
            }
            else{
                if (stripos($comentario, "ford") !== false) {
                    $curl2 = curl_init();
                    $message = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from, // Número de teléfono del destinatario
                        "type" => "interactive",
                        "interactive" => [
                            "type" => "list",
                            "body" => [
                                "text" => "Cual es la nave de tu preferencia:"
                            ],
                            "footer" => [
                                "text" => "Elige una opción para continuar"
                            ],
                            "action" => [
                                "button" => "Ver Referencias",
                                "sections" => [
                                    [
                                        "title" => "Vehiculos Ford",
                                        "rows" => [
                                            [
                                                "id" => "retoma_1",
                                                "title" => "Ford",
                                                "description" => "Ford ranger"
                                            ],
                                            [
                                                "id" => "retoma_2",
                                                "title" => "Ford",
                                                "description" => "Ford scape ecoobost"
                                            ],
                                            [
                                                "id" => "retoma_3",
                                                "title" => "Ford",
                                                "description" => "ord scape hibrida  "
                                            ],
                                            [
                                                "id" => "retoma_4",
                                                "title" => "Ford",
                                                "description" => "ford bronco "
                                            ],
                                            [
                                                "id" => "retoma_5",
                                                "title" => "Ford",
                                                "description" => "ford f150 "
                                            ],
                                            [
                                                "id" => "retoma_6",
                                                "title" => "Ford",
                                                "description" => "ford f150 hibrida "
                                            ],
                                            [
                                                "id" => "retoma_7",
                                                "title" => "Ford",
                                                "description" => "ford f150 raptor "
                                            ],
                                            [
                                                "id" => "retoma_8",
                                                "title" => "Ford",
                                                "description" => "ford ranger raptor  "
                                            ],
                                            [
                                                "id" => "retoma_9",
                                                "title" => "Ford",
                                                "description" => "ford big bronco  "
                                            ],
                                            [
                                                "id" => "retoma_10",
                                                "title" => "Ford",
                                                "description" => "ford mustang  "
                                            ],
                                            
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    curl_setopt_array($curl2, array(
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                        ),
                    ));
                    $response = curl_exec($curl2);
                    curl_close($curl2);
                } elseif (stripos($comentario, "multimarca") !== false) {
                    $curl2 = curl_init();
                    $message = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from,
                        "type" => "interactive",
                        "interactive" => [
                            "type" => "button",
                            "body" => [
                                "text" => "perfecto !!! Contamos con un amplio inventario, finalizando la conversación te envio el link de la pagina donde puedes ver algunos de los vehiculos que tenemos disponibles.\nDeseas dejar tu vehiculo en parte de pago ? "
                            ],
                            "action" => [
                                "buttons" => [
                                    [
                                        "type" => "reply",
                                        "reply" => [
                                            "id" => "retomaSi",
                                            "title" => "Si"
                                        ]
                                    ],
                                    [
                                        "type" => "reply",
                                        "reply" => [
                                            "id" => "retomaNo",
                                            "title" => "No"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    curl_setopt_array($curl2, array(
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                        ),
                    ));
                    $response = curl_exec($curl2);
                    curl_close($curl2);
                } elseif (strpos($comentario, "retoma") !== false) {
                    $curl2 = curl_init();
                    $message = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from,
                        "type" => "interactive",
                        "interactive" => [
                            "type" => "button",
                            "body" => [
                                "text" => "perfecto !!! Deseas dejar tu vehiculo en parte de pago ? "
                            ],
                            "action" => [
                                "buttons" => [
                                    [
                                        "type" => "reply",
                                        "reply" => [
                                            "id" => "retoSi",
                                            "title" => "Si"
                                        ]
                                    ],
                                    [
                                        "type" => "reply",
                                        "reply" => [
                                            "id" => "retoNo",
                                            "title" => "No"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    curl_setopt_array($curl2, array(
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                        ),
                    ));
                    $response = curl_exec($curl2);
                    curl_close($curl2);
                } 
                elseif (strpos($comentario, "retoSi") !== false) {
                    $curl = curl_init();
                    //mensaje de presentacion 
                    $data = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from,
                        "type" => "text",
                        "text" => [
                            "preview_url" => false,
                            "body" => "Porfavor ayudame con la informacion de tu auto. \n Referencia:"
                        ]
                    ];
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($data),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                        ),
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->ferencias = 1;
                    $contacto->save();
                } 
                elseif (strpos($comentario, "retoNo") !== false) {
                    
                    $curl = curl_init();
                    //mensaje de presentacion 
                    $message = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from,
                        "type" => "interactive",
                        "interactive" => [
                            "type" => "button",
                            "body" => [
                                "text" => "Quisiera saber como deseas hacer el negocio "
                            ],
                            "action" => [
                                "buttons" => [
                                    [
                                        "type" => "reply",
                                        "reply" => [
                                            "id" => "contado1",
                                            "title" => "De Contado"
                                        ]
                                    ],
                                    [
                                        "type" => "reply",
                                        "reply" => [
                                            "id" => "financiado1",
                                            "title" => "Financiado"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => json_encode($message),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
                        ),
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->ferencias = 1;
                    $contacto->ferencias = 1;
                    $contacto->modelo = 1;  
                    $contacto->kilometraje = 1;  
                    $contacto->color = 1;   
                    $contacto->precio_estimado = 1;
                    $contacto->negocio = 1;
                    $contacto->save();
                } 
                else {
                    $respuesta = "No entendimos tu mensaje porfa coloca un numero del menu, si deseas volver a ver el menu escribe la palabra 'menu'";
                }
            }
        }
        $curl = curl_init();
        //mensaje de presentacion 
        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $from,
            "type" => "text",
            "text" => [
                "preview_url" => false,
                "body" => $respuesta
            ]
        ];



        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
            ),
        ));


        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            // Crear mensaje
            messages_chat::create([
                'telefono' => '573184482848',
                'message' => $respuesta,
                'timestamp_message' => time(),
                'id_telefono' => $id_telefono,
                'send' => 1,
                'empresas' => 8
            ]);
        }
    }
}
