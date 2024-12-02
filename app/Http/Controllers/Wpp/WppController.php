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
            $token = $req['hub_verify_token'];
            $challenge = $req['hub_chanllenge'];
    
            if(isset($challenge) && isset($token) && $token === token)
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
    function wppPost(Request $request)
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input,true);
        
        return response()->send("EVENT_RECEIVED");
    }
    function wppGet(Request $request)
    {
        if(isset($req['hub_mode']) && isset($req['hub_verify_token']) && isset($req['hub_challenge']) && $req['hub_mode'] === "subscribe" && $req['hub_verify_token'] === token)
        {
            return response()->send($req['hub_challenge']);
        }else{
            return response()->json([],403);
        }
    }


}