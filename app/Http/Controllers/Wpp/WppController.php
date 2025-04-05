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
        
        try {
            $comentario = '';
            $from = 0;
            /* MEnsaje respuesta de texto o mensaje simple  */
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
                    $this->saveMessgeRecive($message, $id_telefono, $empresas, $telefono);
                    if ($contacto->isEmpty()) {
                        $this->botMessage($message, $from, $id_telefono, 1,$config_chat->token_permanente,$config_chat->empresas);
                    } else {
                        $contactovalidation = contactos_chat::where('telefono', $telefono)->where('empresas', $empresas)->first();
                        /* $contactovalidation->bot == 1 | 0  */
                        /* 1 = nuevo, 0 = ya se mando el primer mensaje  */
                        Log::info("Sengundo mensaje texto  mesaje = $message");
                        $this->botMessage($message, $from, $id_telefono, $contactovalidation->bot,$config_chat->token_permanente,$config_chat->empresas);
                    }
                }
            } else {
                /* Mensaje respuesta de botones */
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
                        $contactovalidation = contactos_chat::where('telefono', $from)->where('empresas', $empresas)->first();
                        $this->saveMessgeRecive($buttonTitle, $id_telefono, $empresas, $from);
                        $this->botMessage($buttonId, $from, $id_telefono, $contactovalidation->bot,$config_chat->token_permanente,$config_chat->empresas);
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
                /* Mensajes respuesta de lista */
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
                    $contactovalidation = contactos_chat::where('telefono', $from)->where('empresas', $empresas)->first();
                    $this->saveMessgeRecive($selected_option_title, $id_telefono, $empresas, $from);
                    $this->botMessage($selected_option_id, $from, $id_telefono, $contactovalidation->bot,$config_chat->token_permanente,$config_chat->empresas);
                } else {
                    Log::warning("⚠️ No se encontró un mensaje válido en la solicitud.");
                }
            }

        } catch (\Throwable $th) {
            return response()->json(['message' => 'EVENT_RECEIVED'], 200);
        }
        return response()->json(['message' => 'EVENT_RECEIVED'], 200);
    }

    function sendMessage(Request $request)
    {

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
            if ($admin == 1) {
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
                    where ch.empresas = " . $empresa . "
                    ORDER BY ult_messag.created_at DESC;
                ");
            } else {
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
                    where id_users = " . $id_user . " and ch.empresas = " . $empresa . "
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
    function botMessage($comentario, $from, $id_telefono, $nuevo,$tokenWhatssApp,$empresas)
    {
        $respuesta = '';
        $telefono = $from;
        /* bot arcamotor */
        if($empresas === 8){
            if ($nuevo == 1) {
    
                $message = "🔹¡Hola, buen día! ☀️\n👋 Mi nombre es Brandon Arbelaez, especialista en el sector financiero 💰 y automotriz 🚗.\n📌 Permíteme hacerte unas preguntas 📝 para poder asesorarte de la mejor manera.\n✨ ¡Estoy aquí para ayudarte!\nDeseas comprar vehiculo?";
                $option = [
                    [
                            "id" => "ford",
                            "title" => "Nuevo FORD"
                    ],
                    [
                            "id" => "multimarca",
                            "title" => "Usado Multimarca"
                    ]
                ];
                $this->sendMessageOptions($telefono, $message, $option, $id_telefono, $tokenWhatssApp, $empresas);
                $contacto = contactos_chat::where('telefono', $telefono)->first();
                $contacto->bot = 0;
                $contacto->save();
            } else {
                $contacto = contactos_chat::where('telefono', $from)->first();
                if ($contacto->finalizado == 1) {
                    return;
                }
                if ($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 0  && $contacto->kilometraje == 0  && $contacto->color == 0   && $contacto->precio_estimado == 0) {
                    $respuesta = "Modelo:";
                    $this->sendMessageText($telefono,$respuesta,$id_telefono,$tokenWhatssApp,$empresas);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->modelo = 1;
                    $contacto->save();
                } elseif ($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 0  && $contacto->color == 0   && $contacto->precio_estimado == 0) {
                    $respuesta = "Kilometraje:";
                    $this->sendMessageText($telefono,$respuesta,$id_telefono,$tokenWhatssApp,$empresas);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->kilometraje = 1;
                    $contacto->save();
                } elseif ($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 0   && $contacto->precio_estimado == 0) {
                    $respuesta = "Color:";
                    $this->sendMessageText($telefono,$respuesta,$id_telefono,$tokenWhatssApp,$empresas);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->color = 1;
                    $contacto->save();
                } elseif ($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 0) {
                    $respuesta = "Precio estimado:";
                    $this->sendMessageText($telefono,$respuesta,$id_telefono,$tokenWhatssApp,$empresas);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->precio_estimado = 1;
                    $contacto->save();
                } elseif ($contacto->negocio == 0 &&  $contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1) {
                    $respuesta = "Quisiera saber como deseas hacer el negocio ";
                    $options = [
                        [
                            "id"=>"contado1",
                            "title"=>"De Contado"
                        ],
                        [
                            "id"=>"financiado1",
                            "title"=>"Financiado"
                        ],
                    ];
                    $this->sendMessageOptions($telefono,$respuesta,$options,$id_telefono,$tokenWhatssApp,$empresas);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->negocio = 1;
                    $contacto->save();
                } elseif ($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1 && stripos($comentario, "contado1") !== false) {
                    $respuesta = "Genial hemos finalizado En unos minutos uno de nuestros Asesores te contactara para continuar el proceso, gracias...";
                    $this->sendMessageText($telefono,$respuesta,$id_telefono,$tokenWhatssApp,$empresas);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->finalizado = 1;
                    $contacto->save();
                } elseif ($contacto->ingresos == 0 &&  $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1 && stripos($comentario, "financiado1") !== false) {
                    $respuesta = "Genial, te podemos ayudar con la financiación, voy hacerte unas preguntas y revisamos la viabilidad. ";
                    $options = [
                        [
                            "id" => "empleado1",
                            "title" => "Soy empleado"
                        ],
                        [
                            "id" => "independiente1",
                            "title" => "Soy Independiente"
                        ]
                        ];
                    $this->sendMessageOptions($telefono,$respuesta,$options,$id_telefono,$tokenWhatssApp,$empresas);
                } elseif ($contacto->negocio == 1 && $contacto->ingresos == 0 && $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1) {
                    $respuesta = "Cual es tu ingreso mensual ? ";
                    $this->sendMessageText($telefono,$respuesta,$id_telefono,$tokenWhatssApp,$empresas);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->ingresos = 1;
                    $contacto->save();
                } elseif ($contacto->negocio == 1 && $contacto->ingresos == 1 && $contacto->ferencias == 1 && $contacto->modelo == 1  && $contacto->kilometraje == 1  && $contacto->color == 1   && $contacto->precio_estimado == 1) {
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
                    $this->sendMessageText($telefono,$respuesta,$id_telefono,$tokenWhatssApp,$empresas);
                    $contacto = contactos_chat::where('telefono', $from)->first();
                    $contacto->finalizado = 1;
                    $contacto->save();
                } else {
                    if (stripos($comentario, "ford") !== false) {
                        $respuesta = "Cual es la nave de tu preferencia:";
                        $options = [
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
                        ];
                        $this->sendMessageListOptions($telefono,$respuesta,"Selecciona",$options,$tokenWhatssApp,$id_telefono,$empresas);
                    } elseif (stripos($comentario, "multimarca") !== false) {
                        $respuesta = "perfecto !!! Contamos con un amplio inventario, finalizando la conversación te envio el link de la pagina donde puedes ver algunos de los vehiculos que tenemos disponibles.\nDeseas dejar tu vehiculo en parte de pago ? ";
                        $options = [
                            [
                                "id" => "retomaSi",
                                "title" => "Si"
                            ],
                            [
                                "id" => "retomaNo",
                                "title" => "No"
                            ]
                        ];
                        $this->sendMessageOptions($telefono,$respuesta,$options,$id_telefono,$tokenWhatssApp,$empresas);
                    } elseif (strpos($comentario, "retoma") !== false) {
                        $respuesta = "perfecto !!! Deseas dejar tu vehiculo en parte de pago ? ";
                        $options = [
                            [
                                "id" => "retoSi",
                                "title" => "Si"
                            ],
                            [
                                "id" => "retoNo",
                                "title" => "No"
                            ]
                        ];
                        $this->sendMessageOptions($telefono,$respuesta,$options,$id_telefono,$tokenWhatssApp,$empresas);
                    } elseif (strpos($comentario, "retoSi") !== false) {
                        $respuesta = "Porfavor ayudame con la informacion de tu auto. \n Referencia:";
                        $this->sendMessageText($telefono,$respuesta,$id_telefono,$tokenWhatssApp,$empresas);
                        $contacto = contactos_chat::where('telefono', $telefono)->first();
                        $contacto->ferencias = 1;
                        $contacto->save();
                    } elseif (strpos($comentario, "retoNo") !== false) {
    
                        $respuesta = "Quisiera saber como deseas hacer el negocio ";
                        $options = [
                            [
                                "id" => "contado1",
                                "title" => "De Contado"
                            ],
                            [
                                "id" => "financiado1",
                                "title" => "Financiado"
                            ]
                        ];
                        $this->sendMessageOptions($telefono,$respuesta,$options,$id_telefono,$tokenWhatssApp,$empresas);
                    } else {
                        $respuesta = "No entendimos tu mensaje porfa coloca un numero del menu, si deseas volver a ver el menu escribe la palabra 'menu'";
                    }
                }
            }
        }
     /*    [
            {
                mensaje:'selecciona el numero de la opcion que necesites. ',
                codigo:1,
                opcion1:'Compra de vehiculo',
                proximo1:3,
                opcion2:'Vender vehiculo',
                proximo2:4,
                tipo :2
    
            },
            // opcion 1 compra vehiculo proximo 3
            {
                mensaje:'Listo, Cuentame mas',
                codigo:3,
                opcion1:'Compra de contado',
                proximo1:5,
                opcion2:'Compra con Financiamiento',
                proximo2:6,
                tipo :2
            },
            {
                mensaje:'¿Cual es el vehiculo que deseas?',
                codigo:5,
                opcion1:'',
                proximo1:7,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Cual es elpresupuesto de dinero que deseas invertir en tu vehiculo?',
                codigo:7,
                opcion1:'',
                proximo1:8,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'!Genial¡ un asesor de nuestro concesionario te contactara en horas laborales. Gracios por contar con nosotros',
                codigo:8,
                opcion1:'',
                proximo1:9,
                opcion2:'',
                proximo2:0,
                tipo :10
            },
            // proximo codigo 4 ultimo codigo 9
            {
                mensaje:'Claro, Cuentame acerca del financiemiento que deseas',
                codigo:6,
                opcion1:'Tengo credito aprobado',
                proximo1:10,
                opcion2:'Quiero que se gestione el credito',
                proximo2:11,
                tipo :2
            },
            {
                mensaje:'¿Cual es el vehiculo que deseas?',
                codigo:10,
                opcion1:'',
                proximo1:12,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Cual es elpresupuesto de dinero que deseas invertir en tu vehiculo?',
                codigo:12,
                opcion1:'',
                proximo1:13,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'!Genial¡ un asesor de nuestro concesionario te contactara en horas laborales. Gracios por contar con nosotros',
                codigo:13,
                opcion1:'',
                proximo1:14,
                opcion2:'',
                proximo2:0,
                tipo :10
            },
            // opcion 2 de financiemiento codigo 4
            {
                mensaje:'¿Cuentas con un reporte negativo en centrales de riesgo?',
                codigo:11,
                opcion1:'Si',
                proximo1:15,
                opcion2:'No',
                proximo2:16,
                tipo :2
            },
            {
                mensaje:'¿Cual es elpresupuesto de dinero que deseas invertir en tu vehiculo?',
                codigo:15,
                opcion1:'',
                proximo1:17,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'!Genial¡ un asesor de nuestro concesionario te contactara en horas laborales. Gracios por contar con nosotros',
                codigo:17,
                opcion1:'',
                proximo1:18,
                opcion2:'',
                proximo2:0,
                tipo :10
            },
            {
                mensaje:'Cuentanos, ¿Cual es tu profesion?',
                codigo:16,
                opcion1:'Empleado',
                proximo1:19,
                opcion2:'Independiente',
                proximo2:25,
                tipo :2
            },
            {
                mensaje:'¿Que tipo de contrato tienes?',
                codigo:19,
                opcion1:'',
                proximo1:20,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Que antiguedad tienes en la empresa?',
                codigo:20,
                opcion1:'',
                proximo1:21,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Cual es tu ingreso mensual?',
                codigo:21,
                opcion1:'',
                proximo1:22,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Cual es el vehiculo que deseas?',
                codigo:22,
                opcion1:'',
                proximo1:23,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¡Genial! Un asesor de nuestro concesionario te contactará en horario laboral, gracias por confiar en nosotros',
                codigo:23,
                opcion1:'',
                proximo1:24,
                opcion2:'',
                proximo2:0,
                tipo :10
            },
            {
                mensaje:'Cuentanos mas de tu actividad,¿ Tienes Camara de comercio?',
                codigo:25,
                opcion1:'',
                proximo1:26,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Tienes Rut?',
                codigo:26,
                opcion1:'',
                proximo1:27,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Declaras renta?',
                codigo:27,
                opcion1:'',
                proximo1:28,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Cual es tu promedio de ingresos mensuales?',
                codigo:28,
                opcion1:'',
                proximo1:29,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¿Que vehiculos quieres?',
                codigo:29,
                opcion1:'',
                proximo1:30,
                opcion2:'',
                proximo2:0,
                tipo :0
            },
            {
                mensaje:'¡Genial! Un asesor de nuestro concesionario te contactará en horario laboral, gracias por confiar en nosotros',
                codigo:30,
                opcion1:'',
                proximo1:31,
                opcion2:'',
                proximo2:0,
                tipo :10
            },
            // opcion 2 venta de vehiculo
            {
                mensaje:'Cuentanos, ¿ Cual es tu vehiculo?',
                codigo:4,
                opcion1:'',
                proximo1:6,
                opcion2:'',
                proximo2:0,
                tipo :0
            }
        ] */
        
        /* bot general */
        Log::info("Empresa es = $empresas   ");
        if($empresas !== 8){
            $messageId = contactos_chat::where("telefono",$telefono)->where('empresas',$empresas)->first();
            if($messageId->mensaje1 === 0 ){
                $messageId->mensaje1 = 1;
                $messageId->save();
                $mensaje = "Hola como estas?, un gusto en antenderte, confirmame en las siguientes opciones de que manera te podemos asesorar para la compra o venta de tu vehiculo";
                $options = [
                    [
                        "id"=>"compra",
                        "title"=>"Compra de vehiculo"
                    ],
                    [
                        "id"=>"venta",
                        "title"=>"Venta de vehiculo"
                    ],
                ];
                $this->sendMessageOptions($telefono,$message,$options,$id_telefono,$tokenWhatssApp,$empresas);
            }else{
                if(stripos($comentario, "compra") !== false){
                    $message ="Listo, Cuentame mas.";
                    $options = [
                        [
                            "id"=>"ccontado",
                            "title"=>"Compra de contado"
                        ],
                        [
                            "id"=>"cfinanciado",
                            "title"=>"Compra de con financiamiento"
                        ],
                    ];
                    $this->sendMessageOptions($telefono,$message,$options,$id_telefono,$tokenWhatssApp,$empresas);
                }
                if(stripos($comentario, "ccontado") !== false){
                    $message ="¿Cual es el vehiculo que deseas?";
                    $this->sendMessageText($telefono,$message,$id_telefono,$tokenWhatssApp,$empresas);
                    $messageId->mensaje2 = 1; 
                    $messageId->save();
                }
                if($messageId->mensaje2 == 1 ){
                    $mensaje = "¿Cual es el presupuesto de dinero que deseas invertir en tu vehiculo?";
                    $this->sendMessageText($telefono,$message,$id_telefono,$tokenWhatssApp,$empresas);
                    $messageId->mensaje2 = 2; 
                    $messageId->mensaje3 = 1; 
                    $messageId->save();
                }
                if($messageId->mensaje3 == 1 ){
                    $mensaje = "!Genial¡ un asesor de nuestro concesionario te contactara en horas laborales. Gracios por contar con nosotros";
                    $this->sendMessageText($telefono,$message,$id_telefono,$tokenWhatssApp,$empresas);
                    $messageId->mensaje3 = 2; 
                    $messageId->finalizado = 1; 
                    $messageId->save();
                }

            }
        }
    }
    function saveMessgeSend($respuesta, $telefonoId, $telefono, $empresa)
    {
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
    function saveMessgeRecive($respuesta, $telefonoId, $empresa, $telefono)
    {
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
        return response()->json(['succes' => 'Estado actualizado con exito']);
    }

    function sendMessageText($telefono, $message, $idTelefono, $privateToken, $empresa)
    {
        Log::alert(" se enviara un mensaje de texto");
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
        /* envio de mensajes a api wpp */
        $this->postMessages($data, $privateToken, $idTelefono);
        $this->saveMessgeSend($message, $idTelefono, $telefono, $empresa);
    }

    function sendMessageOptions($telefono, $tituloOptions, $options, $idTelefono, $privateToken, $empresa)
    {
        $optionButtons = array();
        for ($i = 0; $i < count($options); $i++) {
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
       
        Log::warning("respuesta ". json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        /* envio de mensajes a api wpp */
        $this->postMessages($data, $privateToken, $idTelefono);
        /* se guarda el mensaje enviado */
        $this->saveMessgeSend($tituloOptions, $idTelefono, $telefono, $empresa);
    }
    function sendMessageListOptions($telefono, $message, $titleSections, $optionsSections, $privateToken, $idTelefono, $empresa)
    {
        $options = array();
        for ($i = 0; $i < count($optionsSections); $i++) {
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
        $this->postMessages($data, $privateToken, $idTelefono);
        $this->saveMessgeSend($message, $idTelefono, $telefono, $empresa);
    }
    function postMessages($data, $privateToken, $idTelefono)
    {
        Log::warning("private token  ". $privateToken);
        Log::warning("private token  ". $idTelefono);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://graph.facebook.com/v21.0/$idTelefono/messages",
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
                "Authorization: Bearer $privateToken"
            ),
        ));

        $response = curl_exec($curl);
        $curlError = curl_error($curl);
        curl_close($curl);

        if ($curlError) {
            // Error de cURL
            return [
                'success' => false,
                'error' => $curlError
            ];
        }

        // Intenta decodificar la respuesta
        $decoded = json_decode($response, true);
        Log::warning("respuesta $response");
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Error al decodificar JSON
            return [
                'success' => false,
                'error' => 'JSON decode error: ' . json_last_error_msg(),
                'raw_response' => $response
            ];
        }

        // Verifica si la API devolvió un error
        if (isset($decoded['error'])) {
            return [
                'success' => false,
                'error' => $decoded['error'],
            ];
        }

        // Todo bien
        return [
            'success' => true,
            'response' => $decoded
        ];
    }
}
