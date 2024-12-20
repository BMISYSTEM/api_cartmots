<?php

namespace App\Http\Controllers\Wpp;

use App\Http\Controllers\Controller;
use App\Models\config_chat;
use App\Models\contactos_chat;
use App\Models\messages_chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Define la ruta completa del archivo (puedes usar el helper `storage_path`)
        $filePath = storage_path('./seguimiento.txt');
        // Captura todo el contenido del request como un array
        $requestData = $req->all();

        // Convierte los datos a formato JSON para una mejor representación
        $jsonData = json_encode($requestData);
        // Contenido que deseas escribir
        // Escribir en el archivo (creará el archivo si no existe)
        file_put_contents($filePath, $jsonData, FILE_APPEND);
        /**primera patrte */
        // $input = file_get_contents('php://input');
        // $data = json_decode($input,true);
        /**segunda parte */



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
                            'id_users'=> $config_chat->id_users
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
                } 
                /**envia los mensajes **/
                /* $this->sendMessage($comentario, $from); */
            } else {
                return response()->json(['message' => 'EVENT_RECEIVED'], 200);
            }
            file_put_contents($filePath, "--------message---------", FILE_APPEND);
        } catch (\Throwable $th) {
            file_put_contents($filePath, "Error generado--.$th",FILE_APPEND);
            return response()->json(['message' => 'EVENT_RECEIVED'], 200);
        }

        return response()->json(['message' => 'EVENT_RECEIVED'], 200);
    }

    function sendMessage(Request $request)
    {
        
        $filePath = storage_path('./seguimiento.txt');
        file_put_contents($filePath, "--------numero---------".$request['numero'], FILE_APPEND);
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
        if($response)
        {
            $empresas = Auth::user()->empresas;
            // Crear mensaje
            messages_chat::create([
                'telefono' => $request['numero'],
                'message' => $request['message'],
                'timestamp_message' =>time(),
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
            $contactos = contactos_chat::where('empresas',$empresa)->where('id_users',$id_user)->get();
            return response()->json(['succes'=>$contactos]);
        } catch (\Throwable $th) {
            return response()->json(['error'=>'Error generado '.$th],500);
        }
    }

    function allMessages(Request $request)
    {
        $telefono = $request->query('telefono');
        $empresas = Auth::user()->empresas;
        try {
            $messages = messages_chat::where('telefono',$telefono)->where('empresas',$empresas)->get();
            return response()->json(['succes'=>$messages]);
        } catch (\Throwable $th) {
            return response()->json(['error'=>'error generado en el servidor'.$th],500);
        }
    }

}
