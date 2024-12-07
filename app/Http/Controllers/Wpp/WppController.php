<?php

namespace App\Http\Controllers\Wpp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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
    
            if(isset($challenge) && isset($token) && $token === $tokenapp)
            {
                return response()->json($challenge);
            }else{
                return response()->json(['error'=>'error'],400);
            }
        } catch (\Throwable $th) {
            return response()->json(['error'=>$th],400);
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

       


        try{
            $comentario = '';
            $from = 0;
            if (isset($req['entry'][0]['changes'][0]['value']['messages'][0]['from']) 
                && isset($req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body']) 
                && ($from = $req['entry'][0]['changes'][0]['value']['messages'][0]['from']) 
                && ($comentario = $req['entry'][0]['changes'][0]['value']['messages'][0]['text']['body'])) {
                /**envia los mensajes **/
                $this->sendMessage($comentario,$from); 
            }else{
               return response()->json(['message'=>'EVENT_RECEIVED'],200); 
            }
            file_put_contents($filePath, "--------message---------",FILE_APPEND); 
        }catch  (\Throwable $th){
            file_put_contents($filePath, "Error generado--.$th"); 
            return response()->json(['message'=>'EVENT_RECEIVED'],200);
        }
        
        return response()->json(['message'=>'EVENT_RECEIVED'],200);
    }

    function sendMessage($comentario,$numero)
    {
        $filePath = storage_path('./seguimiento.txt');
        file_put_contents($filePath, "--------numero---------.$numero",FILE_APPEND);
        $curl = curl_init();
        $data = [];
        if(strpos($comentario,"hola") !== false){
            $data = [
                    "messaging_product" => "whatsapp",
                    "recipient_type" => "individual",
                    "to" => $numero,
                    "type" => "text",
                    "text" => [
                        "preview_url" => false,
                        "body" => "hola como estas ? "
                    ]
                ];
        }else if(strpos($comentario,"boton") !== false){
            $data = [
                    
                        "messaging_product"=> "whatsapp",
                        "recipient_type"=>"individual",
                        "to"=> $numero,
                        "type"=> "interactive",
                        "interactive"=> [
                            "type"=>"button",
                            "body"=> [
                                "text"=> "prueba de botones"
                            ],
                            "action"=> [
                                "buttons"=> [
                                    [
                                        "type"=> "reply",
                                        "reply"=> [
                                            "id"=> "button1",
                                            "title"=> "primera opcion"
                                        ]
                                    ],
                                    [
                                        "type"=> "reply",
                                        "reply"=> [
                                            "id"=> "button2",
                                            "title"=>"segunda opcion"
                                        ]
                                    ]
                                ]
                            ]
                        ]

                ];
        }else {
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
        }


            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://graph.facebook.com/v21.0/474070335798438/messages',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>json_encode($data),
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer EAAH7VDWCz74BO0U9OsdlULHEbXupK2u87sSidoZC9UcARVvTqo8ZCYZASVoZCBomljw9yMe3OMZCPN10QcUDEVscZAk1nJW2CoTGQARPP84wmzY1VuSHyed1fFN6gKgdjOvOsIo2rlAv6qHUJwLpTjU6TNmlrVUoGkVEqVtKlcYipCSCs4FpELXMorJA3AOFL6'
              ),
            ));
        
        
        $response = curl_exec($curl);
        curl_close($curl);
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


}