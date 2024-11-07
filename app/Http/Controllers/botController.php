<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use App\Models\conversacionbot;
use App\Models\newchatbot;
use App\Models\numberchatbot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class botController extends Controller
{
    public function createNewBot(Request $request) 
    {
        $empresa = Auth::user()->empresas;
        $validacion = $request->validate(
            [
                'codigo'     => 'required',
                'nombre'     => 'required',
                'descripcion' => 'required',
                'inicio'      => 'required',
                'fin'        => 'required',
            ],
            [
                'codigo.required'       => 'El campo codigo es obligatorio ',
                'nombre.required'       => 'El campo nombre es obligatorio ',
                'descripcion.required'   => 'El campo decripcion es obligatorio ',
                'inicio.required'        => 'El campo inicio es obligatorio ',
                'fin.required'          => 'El campo fin es obligatorio ',
            ]
        );
        
        $campan = newchatbot::create([
            'codigo'        => $validacion['codigo'],
            'nombre'        => $validacion['nombre'],
            'descripcion'    => $validacion['descripcion'],
            'inicio'         => $validacion['inicio'],
            'fin'           => $validacion['fin'],
            'empresa'       => $empresa
        ]);
        return response()->json('true');
    }
    // consulta todos las campañas
    public function index(){
        $empresa = Auth::user()->empresas;
        $inicio = $_GET['inicio'];
        $fin = $_GET['fin'];

        $Query = "
                    select * 
                    from newchatbots nb
                    left join (select count(*) as numerointeracciones,codigo_campana as codigo_campana from numberchatbots GROUP BY codigo_campana) num on nb.codigo = num.codigo_campana
                    where 
                    nb.fin >= '".$inicio."' and nb.activo = 1 and 
                    nb.empresa = ".$empresa."
                ";
        $Vista = DB::select($Query);
        return response()->json($Vista);
        // return $Query;
    }
    // consulta la informacion de una campaña 
    public function find() 
    {
        $codigo = $_GET['codigo'];
        $campana = DB::select(" SELECT 	nc.nombre,nc.codigo,nc.descripcion,nc.inicio,nc.fin,nc.activo,
                                        e.nombre as nomepresa,e.logo
                                FROM newchatbots nc
                                inner join empresas e on nc.empresa = e.id
                                where codigo = '".$codigo."'");
        return response()->json($campana);
    }
    // consulta los chasts de una campaña
    public function listchat(){
        $codigo = $_GET['codigo'];
        $Query = DB::select("select * from numberchatbots where codigo_campana ='".$codigo."'" );
        return response()->json($Query);
    }
    // consulta un numero 
    public function consulta()
    {
        $telefono = $_GET['telefono'];
        $campana = $_GET['campana'];
        $consulta = DB::select("select * from numberchatbots where telefono = '".$telefono."' and codigo_campana = '".$campana."'");
        if($consulta)
        {
            return response()->json($consulta);
        }else
        {
            return 0;
        }
    }
    // guarda el chat completo 
    public function savechat(Request $request)
    {
        // gusradr el numero del chat ingresado
        $numero = $request['telefono'];//almaceno el trelefono digitado el cual debe estar validado de que no exista en la lista con la misma campaña
        $campana = $request['codigo'];//almacenamos el codigo de la campaña a la cual se agregara la conversacion
        $codigo_chat = $request['codigo_chat'];//codigo que se utilizara para enlazar la conversacion con el chat y la publicidad
        $conversacion = $request['conversacion'];//se almacena la conversacion para hacer un foreach e insertarlos en la base de datos
        // guardar la campaña numero
        $number_campan = numberchatbot::create([
            'telefono'        =>$numero,
            'codigo_campana'=>$campana,
            'codigo_chat'   =>$codigo_chat
        ]);
        // recorro el arreglo de conversaciones
        foreach($conversacion as $mensaje)
        {
                $conversacion_new = conversacionbot::create([
                    'codigo_chat'       =>$codigo_chat,
                    'codigo_mensaje'    =>$mensaje['codigo'],
                    'mensaje'           =>$mensaje['mensaje'],
                    'opcion1'           =>empty($mensaje['opcion1']) ? '-' : $mensaje['opcion1'],
                    'proximo1'          =>$mensaje['proximo1'],
                    'opcion2'           =>empty($mensaje['opcion2']) ? '-' : $mensaje['opcion2'],
                    'proximo2'          =>$mensaje['proximo2'],
                    'tipo'              =>$mensaje['tipo'],
                ]);
        }
        return $request;
    }

    public function findcampana(){
        $campana = $_GET['codigo'];
        $Query = DB::select("Select * from newchatbots where codigo = '".$campana."'");
        return response()->json($Query);
    }

    public function updatecampana(Request $request){
        $post = $request->validate(
            [
                'nombre' => 'required',
                'descripcion' =>'required',
                'codigo' => 'required'
            ],
            [
                'nombre.required'=> 'El nombre es obligatorio',
                'descripcion.required'=>'La descripcion es requerida',
                'codigo.required'=>'El codigo por algun motivo no se envio'
            ]
            );
        $campana = newchatbot::where('codigo', $post['codigo'])->first();
        $campana->nombre = $post['nombre'];
        $campana->descripcion = $post['descripcion'];
        $campana->save();
        return response()->json(['200'=>'Se actualizo de forma correcta']);
    }

    public function createclientebot(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $post = $request->validate(
            [
                'id_user' => 'required',
                'codigo_chat' => 'required',
                'codigo' =>'required'
            ],
            [
                'id_user.required' => 'Se requiere que seleccione un asesor'
            ]
            );
        $numero = numberchatbot::where('codigo_chat',$post['codigo_chat'])->first();
        $nombreCliente = conversacionbot::where('codigo_chat',$post['codigo_chat'])->first(); 
        $Query = DB::select("Select count(*) as numero from clientes where empresas = '".$empresa."' and telefono = '".$numero['telefono']."'");

        if($Query[0]->numero === 0){
            $create = cliente::create([
                'nombre'=> $nombreCliente['mensaje'],
                'apellido'=> '--',
                'cedula'=> '--',
                'date'=> null,
                'telefono'=> $numero['telefono'],
                'email'=> '--',
                'estados'=> 1,
                'users_id'=> $post['id_user'],
                'TIPO_DOCUMENTO'=> '--',
                'FECHA_EXPEDICCION'=> null,
                'ciudad'=> '--',
                'genero'=> '--',
                'estado_civil'=> '--',
                'direccion'=> '--',
                'celular'=> '--',
                'tipo_vivienda'=> '--',
                'antiguedad_vivienda'=> 0,
                'empresas'=> $empresa
            ]);
            $numero->estado = 1;
            $numero->save();
        }else{
            return response()->json(['500'=>'El cliente ya esta asignado a un asesor, esto se debe a que el cliente interactuo con el bot pero ya estaba asignado con un asesor']);
        }

        return response()->json(['200'=>'Se creo el cliente de forma correcat ']);
        // return response()->json(['200'=>$Query[0]->numero]);
    }

    public function asesores()
    {
        $empresa = Auth::user()->empresas;
        $asesores = DB::select("Select * from users where empresas = '".$empresa."'");
        return response()->json($asesores);
    }

    public function mensajes()
    {
        $codigo = $_GET['codigo'];
        $Query = DB::select("select * from conversacionbots where codigo_chat = '".$codigo."'");
        return response()->json($Query);
    }

    public function campanaseguimiento()
    {
        $id = $_GET['id'];

        $Query = DB::select("select telefono from clientes where id= '".$id."'");
        $telefono = $Query[0]->telefono;
        $QueryCampanas = DB::select("select * from numberchatbots where telefono ='".$telefono."'" );
        $QueryChats = [];
        foreach($QueryCampanas as $campanas)
        {
            
            $QueryChat = DB::select("select * from conversacionbots where codigo_chat = '". $campanas->codigo_chat."'");
            $QueryChats[] = $QueryChat;
        }
        return response()->json(["campanas"=>$QueryCampanas,"conversaciones"=>$QueryChats]);
    }
    public function chatbotone() 
    {
        $codigo = $_GET['codigo'];
        $QueryChat = DB::select("select * from conversacionbots where codigo_chat = '". $codigo."'");
        return response()->json($QueryChat);
    }
}
