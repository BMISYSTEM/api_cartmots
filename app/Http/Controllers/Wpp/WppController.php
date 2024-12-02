<?php

namespace App\Http\Controllers\Wpp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        $archivo = fopen('logwpp.txt',"a");
        $texto = json_encode($req);
        fwrite($archivo,$texto);
        fclose($archivo);
        return response()->send("EVENT_RECEIVED");
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