<?php

namespace App\Http\Controllers\Wpp;

use App\Http\Controllers\Controller;
use App\Models\config_chat;
use App\Models\contactos_chat;
use App\Models\messages_chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WppController extends Controller
{
    const token = "WPPAPLICATION";
    const webhook_url = "https://public.cartmots.com/api/wpp";
    const llaveAuto2 = "EAASi45ruqf4BO56CoDj68YpO61OpJ6geb9Kes6ZCu9IueTFaPvs2c869T4LPCYTIKtdycMcXIwMer46oMYMafoIShd4SVDQZBcANv4mvebLDI8ZBinC889XeGHL3UcBzwLozIzcwpMlnDGK9hknlsvGywnYZArxQdu2vDnzpmWmVAHF0yWUmGRoCJHOKdeO91AZDZD";
    
    
    
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
                    $this->saveMessgeRecive($message,$id_telefono,$empresas,$telefono);
                    if ($contacto->isEmpty()) {
                        Log::info("opcion=> ",["primera opcion contacto es empty "]);
                        $this->botMessage($message, $from, $id_telefono, 1);
                    } else{
                        $contactovalidation = contactos_chat::where('telefono', $telefono)->where('empresas', $empresas)->first();
                        /* $contactovalidation->bot == 1 | 0  */
                        /* 1 = nuevo, 0 = ya se mando el primer mensaje  */
                        Log::info("opcion=> ",["segunda opcion contacto es 1 "]);
                        $this->botMessage($message, $from, $id_telefono, $contactovalidation->bot);
                    }
                }
            } else {
                
                if (
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['from']) &&
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['type']) &&
                    ($from = $req['entry'][0]['changes'][0]['value']['messages'][0]['from']) &&
                    ($type = $req['entry'][0]['changes'][0]['value']['messages'][0]['type']) &&
                    ($id_telefono = $req['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'])
                ) {

                    if (
                        $type === 'interactive' &&
                        isset($req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['id']) &&
                        isset($req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title']) &&
                        ($buttonId = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['id']) &&
                        ($buttonTitle = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['button_reply']['title'])
                    ) {
                        $config_chat = config_chat::where('id_telefono', $id_telefono)->first();
                        $empresas = $config_chat ? $config_chat->empresas : null;
                        $this->saveMessgeRecive($buttonTitle,$id_telefono,$empresas,$from);
                        $this->botMessage($buttonId, $from, $id_telefono, 0);
                    } elseif (
                        $type === 'text' &&
                        isset($req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) &&
                        ($comentario = $req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])
                    ) {
                    
                        return response()->json([
                            'status' => 'success',
                            'message' => "El usuario $from envió el mensaje: $comentario"
                        ]);
                    }
                }
                if (
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['from']) &&
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['id']) &&
                    isset($req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['title']) &&
                    ($id_telefono = $req['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'])
                ) {
                    // 📌 Extraer información relevante
                    $from = $req['entry'][0]['changes'][0]['value']['messages'][0]['from']; // Número de teléfono del remitente
                    $name = $req['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name'] ?? 'Desconocido'; // Nombre del usuario
                    $message_id = $req['entry'][0]['changes'][0]['value']['messages'][0]['id']; // ID del mensaje
                    $selected_option_id = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['id']; // ID de la opción elegida
                    $selected_option_title = $req['entry'][0]['changes'][0]['value']['messages'][0]['interactive']['list_reply']['title']; // Texto de la opción elegida
                    $receiver_number = $req['entry'][0]['changes'][0]['value']['metadata']['display_phone_number']; // Número que recibió el mensaje

                    $config_chat = config_chat::where('id_telefono', $id_telefono)->first();
                    $empresas = $config_chat ? $config_chat->empresas : null;
                    $this->saveMessgeRecive($selected_option_title,$id_telefono,$empresas,$from);
                    $this->botMessage($selected_option_id, $from, $receiver_number, 0);
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
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
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
        $admin = Auth::user()->rol;
        $contactos = "";
        try {
            if($admin == 1 )
            {
                $contactos = DB::select("
                    SELECT 
                        ch.id,
                        ch.id_users,
                        ch.nombre,
                        ch.telefono,
                        ch.id_telefono,
                        ch.empresas,
                        ch.estado,
                        ult_messag.message,
                        ult_messag.created_at,
                        ((ch.ferencias + ch.modelo + ch.kilometraje + ch.color + ch.precio_estimado + ch.ingresos + ch.negocio) / 7 ) * 100 as 	puntuacion
                    FROM contactos_chats ch
                    INNER JOIN (
                        SELECT m1.*
                        FROM messages_chats m1
                        INNER JOIN (
                            SELECT telefono, MAX(created_at) AS max_created_at
                            FROM messages_chats
                            GROUP BY telefono
                        ) m2 ON m1.telefono = m2.telefono AND m1.created_at = m2.max_created_at
                    ) ult_messag ON ch.telefono = ult_messag.telefono
                    where ch.empresas = ".$empresa."
                    ORDER BY ult_messag.created_at DESC;
                ");
            }else{
                $contactos = DB::select("
                    SELECT 
                        ch.id,
                        ch.id_users,
                        ch.nombre,
                        ch.telefono,
                        ch.id_telefono,
                        ch.empresas,
                        ch.estado,
                        ult_messag.message,
                        ult_messag.created_at,
                        ((ch.ferencias + ch.modelo + ch.kilometraje + ch.color + ch.precio_estimado + ch.ingresos + ch.negocio) / 7 ) * 100 as 	puntuacion
                    FROM contactos_chats ch
                    INNER JOIN (
                        SELECT m1.*
                        FROM messages_chats m1
                        INNER JOIN (
                            SELECT telefono, MAX(created_at) AS max_created_at
                            FROM messages_chats
                            GROUP BY telefono
                        ) m2 ON m1.telefono = m2.telefono AND m1.created_at = m2.max_created_at
                    ) ult_messag ON ch.telefono = ult_messag.telefono
                    where id_users = ".$id_user." and ch.empresas = ".$empresa."
                    ORDER BY ult_messag.created_at DESC;
                ");
            }
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
        $configChat = config_chat::where('id_telefono',$id_telefono)->firs();
        $tokenWhatssApp = $configChat->id_telefono;
        $empresa = $configChat->empresas;
        $telefono = $from;
        if ($nuevo == 1) {

            $message = "🔹¡Hola, buen día! ☀️\n👋 Mi nombre es Brandon Arbelaez, especialista en el sector financiero 💰 y automotriz 🚗.\n📌 Permíteme hacerte unas preguntas 📝 para poder asesorarte de la mejor manera.\n✨ ¡Estoy aquí para ayudarte!\nDeseas comprar vehiculo?";
            $option = [
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
            ];
            $this->sendMessageOptions($telefono,$message,$option,$id_telefono,$tokenWhatssApp,$empresa);
            // $message = [
            //     "messaging_product" => "whatsapp",
            //     "recipient_type" => "individual",
            //     "to" => $from,
            //     "type" => "interactive",
            //     "interactive" => [
            //         "type" => "button",
            //         "body" => [
            //             "text" => $respuesta
            //         ],
            //         "action" => [
            //             "buttons" => [
            //                 [
            //                     "type" => "reply",
            //                     "reply" => [
            //                         "id" => "ford",
            //                         "title" => "Nuevo FORD"
            //                     ]
            //                 ],
            //                 [
            //                     "type" => "reply",
            //                     "reply" => [
            //                         "id" => "multimarca",
            //                         "title" => "Usado Multimarca"
            //                     ]
            //                 ]
            //             ]
            //         ]
            //     ]
            // ];
            // curl_setopt_array($curl2, array(
            //     CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
            //     CURLOPT_RETURNTRANSFER => true,
            //     CURLOPT_ENCODING => '',
            //     CURLOPT_MAXREDIRS => 10,
            //     CURLOPT_TIMEOUT => 0,
            //     CURLOPT_FOLLOWLOCATION => true,
            //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            //     CURLOPT_CUSTOMREQUEST => 'POST',
            //     CURLOPT_POSTFIELDS => json_encode($message, JSON_UNESCAPED_UNICODE), // Corrección aquí
            //     CURLOPT_HTTPHEADER => array(
            //         'Content-Type: application/json',
            //         'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
            //     ),
            // ));
            // $response = curl_exec($curl2);
            // curl_close($curl2);
            $contacto = contactos_chat::where('telefono', $telefono)->first();
            $contacto->bot = 0;
            $contacto->save();
            // $this->saveMessgeSend($respuesta,$id_telefono,$from);
        } else {
            $contacto = contactos_chat::where('telefono', $from)->first();
            if($contacto->finalizado == 1){
                return;
            }
            if($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 0  && $contacto->kilometraje == 0  && $contacto->color == 0   && $contacto->precio_estimado == 0){
                $curl = curl_init();
                $respuesta = "Modelo:";
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
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
        
        
                $response = curl_exec($curl);
                curl_close($curl);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->modelo = 1;
                $contacto->save();
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 0  && $contacto->color == 0   && $contacto->precio_estimado == 0){
                $curl = curl_init();
                $respuesta = "Kilometraje:";
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
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
        
        
                $response = curl_exec($curl);
                curl_close($curl);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->kilometraje = 1;
                $contacto->save();
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }
            elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 0   && $contacto->precio_estimado == 0){
                $curl = curl_init();
                $respuesta ="Color:";
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
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
        
        
                $response = curl_exec($curl);
                curl_close($curl);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->color = 1;
                $contacto->save();
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }
            elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 0){
                $curl = curl_init();
                $respuesta ="Precio estimado:";
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
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
        
        
                $response = curl_exec($curl);
                curl_close($curl);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->precio_estimado = 1;
                $contacto->save();
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }
            elseif($contacto->negocio == 0 &&  $contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1){
                $curl2 = curl_init();
                $respuesta = "Quisiera saber como deseas hacer el negocio ";
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "interactive",
                    "interactive" => [
                        "type" => "button",
                        "body" => [
                            "text" => $respuesta
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
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->negocio = 1;
                $contacto->save();
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }
            elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1 && stripos($comentario, "contado1") !== false ){
                $curl2 = curl_init();
                $respuesta = "Genial hemos finalizado En unos minutos uno de nuestros Asesores te contactara para continuar el proceso, gracias...";
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => $respuesta
                    ]
                ];
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->finalizado = 1;
                $contacto->save();
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }
            elseif($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1 && stripos($comentario, "financiado1") !== false ){
                $curl2 = curl_init();
                $respuesta = "Genial, te podemos ayudar con la financiación, voy hacerte unas preguntas y revisamos la viabilidad. ";
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "interactive",
                    "interactive" => [
                        "type" => "button",
                        "body" => [
                            "text" => $respuesta
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
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }
            elseif($contacto->negocio == 1 && $contacto->ingresos == 0 && $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1){
                $curl2 = curl_init();
                $respuesta = "Cual es tu ingreso mensual ? ";
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => $respuesta
                    ]
                ];
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->ingresos = 1;
                $contacto->save();
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }
            elseif($contacto->negocio == 1 && $contacto->ingresos == 1 && $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1){
                $curl2 = curl_init();
                $respuesta = "¡SUPER! De acuerdo con lo que me cuentas, es muy probable que tu crédito sea aprobado. \n\nPor favor, déjame estos datos para enviar tu solicitud a estudio. En unas horas me contactaré contigo con una respuesta:";
                
                $respuesta .= "\n\n🔹 *Datos requeridos:*"
                            . "\n- Nombre completo y número de cédula"
                            . "\n- Dirección de residencia"
                            . "\n- Ciudad"
                            . "\n- Nombre de la empresa"
                            . "\n- Dirección de la empresa"
                            . "\n- Antigüedad en la empresa"
                            . "\n- 1 referencia familiar (nombre y teléfono)"
                            . "\n- 1 referencia personal (nombre y teléfono)";
                $message = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $from,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => $respuesta
                    ]
                ];
                curl_setopt_array($curl2, array(
                    CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                        'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                    ),
                ));
                $response = curl_exec($curl2);
                curl_close($curl2);
                $contacto = contactos_chat::where('telefono', $from)->first();
                $contacto->finalizado = 1;
                $contacto->save();
                $this->saveMessgeSend($respuesta,$id_telefono,$from);
            }
            else{
                if (stripos($comentario, "ford") !== false) {
                    $curl2 = curl_init();
                    $respuesta = "Cual es la nave de tu preferencia:";
                    $message = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from, // Número de teléfono del destinatario
                        "type" => "interactive",
                        "interactive" => [
                            "type" => "list",
                            "body" => [
                                "text" => $respuesta
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
                                                "title" => "Ford ranger",
                                                "description" => ""
                                            ],
                                            [
                                                "id" => "retoma_2",
                                                "title" => "Ford scape ecoobost",
                                                "description" => ""
                                            ],
                                            [
                                                "id" => "retoma_3",
                                                "title" => "Ford scape hibrida ",
                                                "description" => " "
                                            ],
                                            [
                                                "id" => "retoma_4",
                                                "title" => "Ford bronco",
                                                "description" => " "
                                            ],
                                            [
                                                "id" => "retoma_5",
                                                "title" => "Ford f150 ",
                                                "description" => ""
                                            ],
                                            [
                                                "id" => "retoma_6",
                                                "title" => "Ford f150 hibrida ",
                                                "description" => ""
                                            ],
                                            [
                                                "id" => "retoma_7",
                                                "title" => "Ford f150 raptor",
                                                "description" => " "
                                            ],
                                            [
                                                "id" => "retoma_8",
                                                "title" => "Ford ranger raptor ",
                                                "description" => " "
                                            ],
                                            [
                                                "id" => "retoma_9",
                                                "title" => "Ford big bronco ",
                                                "description" => " "
                                            ],
                                            [
                                                "id" => "retoma_10",
                                                "title" => "Ford mustang ",
                                                "description" => " "
                                            ],
                                            
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ];
                    curl_setopt_array($curl2, array(
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                            'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                        ),
                    ));
                    $response = curl_exec($curl2);
                    curl_close($curl2);
                    $this->saveMessgeSend($respuesta,$id_telefono,$from);
                } elseif (stripos($comentario, "multimarca") !== false) {
                    $curl2 = curl_init();
                    $respuesta = "perfecto !!! Contamos con un amplio inventario, finalizando la conversación te envio el link de la pagina donde puedes ver algunos de los vehiculos que tenemos disponibles.\nDeseas dejar tu vehiculo en parte de pago ? ";
                    $message = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from,
                        "type" => "interactive",
                        "interactive" => [
                            "type" => "button",
                            "body" => [
                                "text" => $respuesta
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
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                            'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                        ),
                    ));
                    $response = curl_exec($curl2);
                    curl_close($curl2);
                    $this->saveMessgeSend($respuesta,$id_telefono,$from);
                } elseif (strpos($comentario, "retoma") !== false) {
                    $curl2 = curl_init();
                    $respuesta = "perfecto !!! Deseas dejar tu vehiculo en parte de pago ? ";
                    $message = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from,
                        "type" => "interactive",
                        "interactive" => [
                            "type" => "button",
                            "body" => [
                                "text" => $respuesta
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
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                            'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                        ),
                    ));
                    $response = curl_exec($curl2);
                    curl_close($curl2);
                    $this->saveMessgeSend($respuesta,$id_telefono,$from);
                } 
                elseif (strpos($comentario, "retoSi") !== false) {
                    $curl = curl_init();
                    $respuesta ="Porfavor ayudame con la informacion de tu auto. \n Referencia:" ;
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
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                            'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
                        ),
                    ));
                    $response = curl_exec($curl);
                    curl_close($curl);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->ferencias = 1;
                    $contacto->save();
                    $this->saveMessgeSend($respuesta,$id_telefono,$from);
                } 
                elseif (strpos($comentario, "retoNo") !== false) {
                    
                    $curl = curl_init();
                    $respuesta = "Quisiera saber como deseas hacer el negocio ";
                    //mensaje de presentacion 
                    $message = [
                        "messaging_product" => "whatsapp",
                        "recipient_type" => "individual",
                        "to" => $from,
                        "type" => "interactive",
                        "interactive" => [
                            "type" => "button",
                            "body" => [
                                "text" => $respuesta
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
                        CURLOPT_URL => 'https://graph.facebook.com/v21.0/585227118006200/messages',
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
                            'Authorization: Bearer EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD'
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
                    $this->saveMessgeSend($respuesta,$id_telefono,$from);
                } 
                else {
                    $respuesta = "No entendimos tu mensaje porfa coloca un numero del menu, si deseas volver a ver el menu escribe la palabra 'menu'";
                }
            }
        }
            
        
    }

    function saveMessgeSend($respuesta,$telefonoId,$telefono,$empresa){
        messages_chat::create([
            'telefono' => $telefono,
            'telefono' => $telefono,
            'message' => $respuesta,
            'timestamp_message' => time(),
            'id_telefono' => $telefonoId,
            'send' => 1,
            'empresas' => $empresa
        ]);
    }
    function saveMessgeRecive($respuesta,$telefonoId,$empresa,$telefono){
        messages_chat::create([
            'telefono' => $telefono,
            'message' => $respuesta,
            'timestamp_message' => time(),
            'id_telefono' => $telefonoId,
            'send' => 0,
            'empresas' => $empresa
        ]);
    }
    function updateEstadoContact(Request $request) 
    {
        $contacto = contactos_chat::find($request['id']);
        $contacto->estado = $request['estado'];
        $contacto->save();
        return response()->json(['succes'=>'Estado actualizado con exito']);
    }

    function sendMessageText($telefono,$message,$idTelefono,$privateToken,$empresa)
    {
        $curl = curl_init();
        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "text",
            "text" => [
                "preview_url" => false,
                "body" => $message
            ]
        ];
        $idTelefonoBrandon = 585227118006200;
        $tokenBrandon = "EAAaOVZBlj55UBO8JEl58zM99tsm7GZBjgA0OZBh65CO7ZCnA82DbP5WfaLcYxfxY2Qr4fI8NvolfPgOZAhpV2bmRD8R1s3JgplJ6ER9xU43pkDS11v2qItVZAosD4YUbL2vr9ox9bhfSPXg8fUEE82zB5aFPBFRDyuoyyzBP6efR8OAgZAKqQAgMJDIJJg6jSI5zAZDZD";
        /* envio de mensajes a api wpp */
        $this->postMessages($data,$privateToken,$idTelefono);
        $this->saveMessgeSend($message,$idTelefono,$telefono,$empresa);
    }

    function sendMessageOptions($telefono,$tituloOptions,$options,$idTelefono,$privateToken,$empresa)
    {
        $optionButtons = array();
        for($i = 0 ;$i > $options.length; $i++ ){
            $optionButtons[] = [
                    "type" => "reply",
                    "reply" => [
                        "id" => $options[$i]['id'],
                        "title" => $options[$i]['title']
                    ]
                    ];

        };
        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "interactive",
            "interactive" => [
                "type" => "button",
                "body" => [
                    "text" => $tituloOptions
                ],
                "action" => [
                    "buttons" => $optionButtons
                ]
            ]
        ];

        /* envio de mensajes a api wpp */
        $this->postMessages($data,$privateToken,$idTelefono);
        /* se guarda el mensaje enviado */
        $this->saveMessgeSend($message,$idTelefono,$telefono,$empresa);
    }
    function sendMessageListOptions($telefono,$message,$titleSections,$optionsSections,$privateToken,$idTelefono,$empresa)
    {
        $options = array();
        for ($i=0; $i < $optionsSections.lengt(); $i++) { 
            $options[] =  [
                "id" => $optionsSections[$i]['id'],
                "title" => $optionsSections[$i]['title'],
                "description" => $optionsSections[$i]['description'] ?? ""
            ];
        }
        $data = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $telefono,
            "type" => "interactive",
            "interactive" => [
                "type" => "list",
                "body" => [
                    "text" => $message
                ],
                "footer" => [
                    "text" => "Elige una opción para continuar"
                ],
                "action" => [
                    "button" => $titleSections,
                    "sections" => [
                        [
                            "title" => $titleSections,
                            "rows" => $options
                        ]
                    ]
                ]
            ]
        ];
        $this->postMessages($data,$privateToken,$idTelefono);
        $this->saveMessgeSend($message,$idTelefono,$telefono,$empresa);
    }
    function postMessages($data,$privateToken,$idTelefono){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => `https://graph.facebook.com/v21.0/$idTelefono/messages`,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE), 
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                `Authorization: Bearer $privateToken`
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
    }
}
