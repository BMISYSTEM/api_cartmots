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
        /**primera patrte */
        // $input = file_get_contents('php://input');
        // $data = json_decode($input,true);
        /**segunda parte */

        // Captura todo el contenido del request como un array
        /* $requestData = $req->all();

        // Convierte los datos a formato JSON para una mejor representación
        $jsonData = json_encode($requestData, JSON_PRETTY_PRINT);

        // Define el nombre del archivo (puedes personalizarlo)
        $fileName = 'logwpp.txt';

        // Guarda el archivo en el directorio `storage/app/`
        Storage::put($fileName, $jsonData); */
        $entry = $req['entry'][0];
        $changes = $entry['changes'][0];
        $value = $changes['value'];
        $objectMessage = $value['message'][0];
        $comentario = $objectMessage['text']['body'];
        $from = $objectMessage['from'];
        $this->sendMessage($comentario,$from);
        return response("EVENT_RECEIVED");
    }

    function sendMessage($comentario,$numero)
    {
        $comentario = strtolower(($comentario));
       
        if(strpos($comentario,'hola')){
            $data = json_encode([
                "messaging_product"=>"whatsapp",
                "recipient_type"=>"individual",
                "to"=>$numero,
                "type"=>"text",
                "text"=>[
                    "preview_url"=>false,
                    "body"=>"hola soy el bot programado por bayron meneses"
                ]
            ]);
        }
        $options = [
            "http"=>[
                "method"=>"POST",
                "header"=>"Content-Type: application/json\r\nAuthorization: Bearer EAAH7VDWCz74BO7B0KFEMAgjsGJJbQOvVLJiqLiZAubng123ZCOA4WiM2ZCxzyNmziS7IlipdynLZBZAkGe0nXBvQY9x6sreG4Eizo4oIYBW6ebJDM6bcqLEosO6RZAxNZBfM86p5F6gf5yJanutUIWLGwZAHoedoXbmwQiHG6LqpsnedfZAaaabZBGitKbPmSjzDdSZA4g9FTZAiCGCPmTghnG119SURPwZDZD\r\n",
                "content"=>$data,
                "ignore_errors"=>true
                ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents('https://graph.facebook.com/v21.0/408992122295321/messages',false,$options);
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