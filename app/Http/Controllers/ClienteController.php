<?php
namespace App\Http\Controllers;
use App\Models\cliente;
use App\Models\notas;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Http\Resources\clientesResource;
use App\Models\clientes_documento;
use App\Models\notificacion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ClienteController extends Controller
{
    // creacion de un nuevo cliente
    public function create(Request $request){
        $vehiculo = $request->validate(
            [
                'telefono' => 'required|numeric',
                'data'=>'required|date',
                'email'=>'nullable',
                'origen'=>'required|numeric',
                'nombre'=>'nullable',
                'apellido'=>'nullable',
                'cedula'=>'nullable',
                'direccion'=>'nullable'
            ],
            [
                'telefono.required'=>'El telefono es obligatorio',
                'origen.required'=>'El origen es obligatorio',
                'origen.numeric'=>'El origen debe ser numerico',
                'data.required'=>'La fecha es obligatoria',
                'data.date'=>'La fecha no tiene el formato valido',
                'telefono.numeric'=>'El telefono digitado no es valido',
            ]
        );
        $user= Auth::user()->id;
        $empresa = Auth::user()->empresas;
        // validar que el telefono ingresado no exista en la tabla 
        $clienteExist = cliente::where('telefono',$vehiculo['telefono'])->where('empresas',$empresa)->get();
        if(count($clienteExist) > 0 ){
            return response()->json(['response'=>['data'=>['errors'=>[['El cliente ya existe en la base de datos']]]]]);
        }
        $email = '';
        if($vehiculo['email'])
        {
            $email = $vehiculo['email'];
        }
        else{
            $email = 'no definido';
        }
        try {
            $creado = cliente::create([
                'nombre' => $vehiculo['nombre'],
                'apellido' => $vehiculo['apellido'],
                'cedula' => $vehiculo['cedula'],
                'date' => $vehiculo['data'],
                'telefono' => $vehiculo['telefono'],
                'email' => $email,
                'estados'=>'1',
                'users_id'=> $user,
                'empresas'=>$empresa,
                'origen' =>$vehiculo['origen'],
                'direccion'=>$vehiculo['direccion']
            ]);
            return response()->json(['succes'=>$creado ]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>'Error inesperado en el servidor: '.$th]);
        }
    }

    // consuilta la informacion de un solo cliente 
    public function cliente()
    {
        $id = $_GET['id'];
        $cliente = cliente::find($id);
        return response()->json($cliente);
    }
    // devuelve el estado de los clientes
    public function index()
    {
        return new clientesResource(cliente::with('user')->with('estado')->with('vehiculo')->get());
    }
    public function infocliente()
    {   
        $empresa = Auth::user()->empresas;
        $cliente = $_GET['id'];
        $infocliente = DB::select(
            '
          select * from  clientes where id = '.$cliente.' and empresas = '.$empresa.' 
            '
        );
        return response()->json($infocliente);
    }
// permite descargar un documento en especial del cliente
    public function descargasdoc(Request $request)
    {
        $documento = $request['documento'];
        $paht = storage_path('app/public/documentos/'.$documento);
        return  response()->download($paht);
        // return $paht;
        // return $request;
    }


    //actualizacionn de estado 
    public function updateEstados (Request $request)
    {
        $estado = $request['id'];
        $cliente = $request['cliente'];
        $update = cliente::where('id',$cliente)->get();
        $update->toQuery()->update([
            'estados' => $estado
        ]);
        $mensaje = 'El cliente '. $request['user_name']. ' cambio de estado a '.$request['nombre_estado'];
        $notificacion = notificacion::create([
            'user_id' => $request['user'],
            'mensaje' => $mensaje,
            'visto' =>'no'
        ]);
        return 'el cambio se realizo de forma correcta';
    }

    public function vendidos()
    {
        $inicio = $_GET['inicio'];
        $fin = $_GET['fin'];
        $empresa = Auth::user()->empresas;
        $modulo_costo = DB::select("SELECT modulo_costos from configuraciones where empresa_id = ".$empresa);
        if($modulo_costo[0]->modulo_costos == 0 )
        {
            $Query = "
            select 
            e.nombre,
            u.name,u.img,u.id as id_asesor,
            c.nombre as cliente,c.telefono,c.email,c.cedula,
            v.created_at,v.comentario 
            from ventas v 
            inner join clientes c on v.clientes = c.id
            inner join empresas e on v.empresa = e.id
            inner join users u on v.usuario = u.id
            where v.empresa = ".$empresa." and v.updated_at BETWEEN '".$inicio."' AND '".$fin."'
            ";
        }else{
            $Query = "
            select 
            e.nombre,
            u.name,u.img,u.id as id_asesor,
            c.nombre as cliente,c.telefono,c.email,c.cedula,
            f.created_at,CONCAT('Finalizado desde el modulo de costos codigo del pedido: ', f.id,' Placa: ',v.placa) comentario 
            FROM negocios f
            inner join vehiculos v on f.vehiculo = v.id
            inner join clientes c on f.cliente = c.id
            inner join empresas e on f.empresas = e.id
            inner join users u on f.asesor = u.id
            where finalizado = 1 and f.empresas = ".$empresa." and f.updated_at BETWEEN '".$inicio."' AND '".$fin."'
            ";
        }
        $vista = DB::select($Query);
        return response()->json($vista);
    }
    public function pendientes()
    {

        $vista = DB::select("select c.id, c.nombre, c.apellido, c.cedula,c.date, c.telefono, c.email, c.vfinanciar, c.ncuotas, c.valormensual, c.doccedula, c.docestratos, c.docdeclaracion, c.docsolicitud, c.created_at, c.updated_at, c.vehiculos, c.estados, c.users_id, c.tasa,
        u.name,u.rol,v.valor,v.placa,e.estado as nombre_estado
        from clientes as c
        inner join users u on c.users_id = u.id
        inner join vehiculos v on c.vehiculos = v.id
        inner join estados e on c.estados = e.id
        where c.estados ='4'");
        return response()->json($vista);
    }
    public function aprobados()
    {

        $vista = DB::select("select c.id, c.nombre, c.apellido, c.cedula,c.date, c.telefono, c.email, c.vfinanciar, c.ncuotas, c.valormensual, c.doccedula, c.docestratos, c.docdeclaracion, c.docsolicitud, c.created_at, c.updated_at, c.vehiculos, c.estados, c.users_id, c.tasa,
        u.name,u.rol,v.valor,v.placa,e.estado as nombre_estado
        from clientes as c
        inner join users u on c.users_id = u.id
        inner join vehiculos v on c.vehiculos = v.id
        inner join estados e on c.estados = e.id
        where c.estados ='5'");
        return response()->json($vista);
    }
    // trae la informacion de seguimiento del asesor si es 0 trae solo la de el y si es 1 consulta todo de la empresa
    public function seguimiento()
    {   
        $inicio = $_GET['inicio'];
        $fin =$_GET['fin'];
        $rol = Auth::user()->rol;
        $user = Auth::user()->id;
        $empresa = Auth::user()->empresas;
        // super administrador
        if($rol == 1)
        {
            $vista = DB::select("
            select 
            c.id as cliente_id, 
            c.nombre,
            c.apellido,
            c.cedula,
            c.direccion,
            c.date,
            c.telefono,
            c.email,
            c.estados,
            c.users_id,
            u.name,
            u.img,
             e.id as estados_id, e.estado, e.created_at, e.updated_at,e.pendiente,e.aprobado,e.rechazado,
             t.comentarios as comentario,t.fecha as fecha,
             e.color as color
             from clientes c
            inner join users u on c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            inner join estados e on e.id = c.estados
            left join (SELECT max(created_at) as ultima_nota,clientes as clientes,empresas as empresas FROM notas GROUP BY clientes,empresas ) as ult on c.id = ult.clientes and ult.empresas = c.empresas
            left join (select proximo_seguimiento fecha,clientes clientes,comentario comentarios,created_at as ult_nota,empresas as empresa from notas GROUP BY clientes,proximo_seguimiento,comentario,created_at,empresas order by proximo_seguimiento desc) as t  on t.clientes = c.id and ult.ultima_nota = t.ult_nota and t.empresa = c.empresas
            where c.empresas= '".$empresa."' and c.transferido = 0 and e.finalizado <> 1   and t.fecha BETWEEN '".$inicio."' AND '".$fin."'
            group by u.name,u.img,c.id,t.comentarios,t.clientes,c.direccion,c.nombre,c.apellido,c.cedula,c.date,c.telefono,c.email,c.estados,c.users_id,e.id,e.estado,e.created_at,e.updated_at,t.fecha,e.color,e.pendiente,e.aprobado,e.rechazado
            ORDER BY  t.fecha DESC limit 500");
        }else{

            $vista = DB::select("
            select 
            c.id as cliente_id, 
            c.nombre,
            c.apellido,
            c.cedula,
            c.direccion,
            c.date,
            c.telefono,
            c.email,
            c.estados,
            c.users_id,
            u.name,
            u.img,
            e.id as estados_id, e.estado, e.created_at, e.updated_at,e.pendiente,e.aprobado,e.rechazado,
             t.comentarios as comentario,t.fecha as fecha,
             e.color as color
             from clientes c
            inner join users u on c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            inner join estados e on e.id = c.estados
                        left join (SELECT max(created_at) as ultima_nota,clientes as clientes,empresas as empresas FROM notas GROUP BY clientes,empresas ) as ult on c.id = ult.clientes and ult.empresas = c.empresas
            left join (select proximo_seguimiento fecha,clientes clientes,comentario comentarios,created_at as ult_nota,empresas as empresa from notas GROUP BY clientes,proximo_seguimiento,comentario,created_at,empresas order by proximo_seguimiento desc) as t  on t.clientes = c.id and ult.ultima_nota = t.ult_nota and t.empresa = c.empresas
            where c.empresas= '".$empresa."' and u.id = ".$user." and c.transferido = 0 and e.finalizado <> 1  and t.fecha BETWEEN '".$inicio."' AND '".$fin."'
            group by u.name,u.img,c.id,t.comentarios,t.clientes,c.nombre,c.apellido,c.cedula,c.date,c.telefono,c.email,c.estados,c.users_id,e.id,e.estado,e.created_at,e.updated_at,t.fecha,e.color,e.pendiente,e.aprobado,e.rechazado
            ORDER BY  t.fecha DESC limit 500");
        }
        return response()->json($vista);
    }
    // trae la informacion de seguimiento del asesor si es 0 trae solo la de el y si es 1 consulta todo de la empresa
    public function finalizados()
    {   
        $inicio = $_GET['inicio'];
        $fin =$_GET['fin'];
        $rol = Auth::user()->rol;
        $user = Auth::user()->id;
        $empresa = Auth::user()->empresas;
        // super administrador
        if($rol == 1)
        {
            $vista = DB::select("
            select 
            c.id as cliente_id, 
            c.nombre,
            c.apellido,
            c.cedula,
            c.date,
            c.telefono,
            c.email,
            c.estados,
            c.users_id,
            u.name,
            u.img,
             e.id as estados_id, e.estado, e.created_at, e.updated_at,e.pendiente,e.aprobado,e.rechazado,
             t.comentarios as comentario,t.fecha as fecha,
             e.color as color
             from clientes c
            inner join users u on c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            inner join estados e on e.id = c.estados
            left join (SELECT max(created_at) as ultima_nota,clientes as clientes FROM notas GROUP BY clientes ) as ult on c.id = ult.clientes
            left join (select proximo_seguimiento fecha,clientes clientes,comentario comentarios,created_at as ult_nota from notas GROUP BY clientes,proximo_seguimiento,comentario,created_at order by proximo_seguimiento desc) as t  on t.clientes = c.id and ult.ultima_nota = t.ult_nota
            where em.id= '".$empresa."' and c.transferido = 0 and e.finalizado = 1 and e.vendido <> 1  and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            group by u.name,u.img,c.id,t.comentarios,t.clientes,c.nombre,c.apellido,c.cedula,c.date,c.telefono,c.email,c.estados,c.users_id,e.id,e.estado,e.created_at,e.updated_at,t.fecha,e.color,e.pendiente,e.aprobado,e.rechazado
            ORDER BY  fecha DESC");
        }else{

            $vista = DB::select("
            select 
            c.id as cliente_id, 
            c.nombre,
            c.apellido,
            c.cedula,
            c.date,
            c.telefono,
            c.email,
            c.estados,
            c.users_id,
            u.name,
            u.img,
            e.id as estados_id, e.estado, e.created_at, e.updated_at,e.pendiente,e.aprobado,e.rechazado,
             t.comentarios as comentario,t.fecha as fecha,
             e.color as color
             from clientes c
            inner join users u on c.users_id = u.id
            inner join empresas em on u.empresas = em.id
            inner join estados e on e.id = c.estados
            left join (SELECT max(created_at) as ultima_nota,clientes as clientes FROM notas GROUP BY clientes ) as ult on c.id = ult.clientes
            left join (select proximo_seguimiento fecha,clientes clientes,comentario comentarios,created_at as ult_nota from notas GROUP BY clientes,proximo_seguimiento,comentario,created_at order by proximo_seguimiento desc) as t  on t.clientes = c.id and ult.ultima_nota = t.ult_nota
            where em.id= '".$empresa."' and u.id = ".$user." and c.transferido = 0 and e.finalizado = 1 and e.vendido <> 1 and c.created_at BETWEEN '".$inicio."' AND '".$fin."'
            group by u.name,u.img,c.id,t.comentarios,t.clientes,c.nombre,c.apellido,c.cedula,c.date,c.telefono,c.email,c.estados,c.users_id,e.id,e.estado,e.created_at,e.updated_at,t.fecha,e.color,e.pendiente,e.aprobado,e.rechazado
            ORDER BY  fecha DESC");
        }
        return response()->json($vista);
        // comentario de prueba
    }
    public function seguimiento_centro()
    {
        $rol = Auth::user()->rol;
        $user = Auth::user()->id;
        $empresa = Auth::user()->empresas;
        $vista = DB::select("
        select 
        c.id as cliente_id, 
        c.nombre,
        c.apellido,
        c.cedula,
        c.direccion,
        c.date,
        c.telefono,
        c.email,
        c.estados,
        c.users_id,
         e.id as estados_id, e.estado, e.created_at, e.updated_at,
         t.comentarios as comentario,t.fecha as fecha,
         e.color as color
         from clientes c
        inner join users u on c.users_id = u.id
        inner join empresas em on u.empresas = em.id
        inner join estados e on e.id = c.estados
        inner join clientes_documentos cd on c.id = cd.cliente and em.id = cd.empresa
        left join (SELECT max(created_at) as ultima_nota,clientes as clientes FROM notas GROUP BY clientes ) as ult on c.id = ult.clientes
        left join (select proximo_seguimiento fecha,clientes clientes,comentario comentarios,created_at as ult_nota from notas GROUP BY clientes,proximo_seguimiento,comentario,created_at order by proximo_seguimiento desc) as t  on t.clientes = c.id and ult.ultima_nota = t.ult_nota
        where 	em.id= ".$empresa."
        		and cd.centrofinanciero = '1'
        group by c.id,t.comentarios,t.clientes,c.direccion,c.nombre,c.apellido,c.cedula,c.date,c.telefono,c.email,c.estados,c.users_id,e.id,e.estado,e.created_at,e.updated_at,t.fecha,e.color
        ORDER BY  fecha DESC");

        return response()->json($vista);
        // comentario de prueba
    }
    public function busqueda(Request $request)
    {   
        $empresa = Auth::user()->empresas;
        $telefono = $request->query('telefono');
        $email = $request->query('email');
        $entroen= '';
        // valida que venga el telefono pero no el email
        if($telefono<>"null" and $email=="null")
        {
            $entroen = 'solo Telefono';
           $result = DB::select("select c.id from clientes c inner join users u on c.users_id = u.id 
            inner join empresas e on u.empresas = e.id
            where c.telefono = '".$telefono."' and e.id = ". $empresa ); 
        }elseif($telefono == "null"  and $email <> "null")
        {   //valida que venga el email pero el telefono no
            $entroen = 'solo email';
            $result = DB::select("select c.id from clientes c inner join users u on c.users_id = u.id 
            inner join empresas e on u.empresas = e.id
            where c.email = '".$email. "' and e.id = ". $empresa ); 
        }elseif($telefono <> "null" and $email<>"null")
        {  //Si viene tanto email como telefono 
            $entroen = 'ambos';
            $result = DB::select("select c.id from clientes c inner join users u on c.users_id = u.id 
            inner join empresas e on u.empresas = e.id
            where c.email = '".$email. "' and c.telefono = '".$telefono. "' and e.id = ". $empresa ); 
        }
        
        if($result){
            return response()->json(['succes'=>$result]);
        }else{
            return response()->json(['nofound'=>0]);
        }
    }

    // administrara los documentos que lleguen de cada cliente
    public function documentos(Request $request) 
    {
        // usuario el cual esta cargando el documento
        $user = Auth::user()->id;
        $empresa = Auth::user()->empresas;
        // capturo el documento que viene en un formdata
        $documento = $request->file('documento')->store('public/documentos');
        //extraigo la url del documento para guardarlo en la base de datos
        $urdocumento = Storage::url($documento);
        // campturamos tipo y el cliente al cual pertenece el documento
        $cliente = $request['cliente'];
        $tipo = $request['tipo'];

        $docbd = clientes_documento::create([
            'usuario' => $user,
            'cliente' => $cliente,
            'empresa' => $empresa,
            'urldoc'  => $urdocumento,
            'tipo'    => $tipo,
            'centrofinanciero'  => $request['centro']
        ]);
        return response()->json($docbd);
    }
    // retorno de lista de documentos del cliente
    public function documentosall()
    {
        $empresa = Auth::user()->empresas;
        $cliente = $_GET['id'];
        $documentos = DB::select('select * from clientes_documentos where cliente = '. $cliente .' and empresa = '. $empresa . ' ;');
        return response()->json($documentos);
    }
    // ediccion de informacion del cliente
    public function updateCliente (Request $request) 
    {   
        $empresas = Auth::user()->empresas;
        $mensaje = '';
        $request->validate(
            [
                'telefono' => 'required|numeric',
                'nombre'=>'nullable',
                'apellido'=>'nullable',
                'cedula'=>'nullable',
                'email'=>'required',
                'direccion'=>'nullable'
            ],
            [
                'telefono.required' => 'El telefono es obligatorio',
                'telefono.numeric' => 'El telefono debe ser numerico',
            ]
        );
        // Se consulta que el numero que se esta tratando de asignar no exista dentro de la misma empresa, para evitar colapsos dentro de la integridad del dato
        $repeatcliente = DB::select('SELECT COUNT(*) as rp FROM clientes WHERE telefono = ' . $request['telefono'] . ' AND empresas = ' . $empresas);
        $cliente = cliente::find($request['id']);
        if( $cliente['telefono'] <> $request['telefono'] ){

            if ($repeatcliente[0]->rp == 0 ){
                
                $cliente->nombre = $request['nombre'];
                $cliente->apellido = $request['apellido'];
                $cliente->cedula = $request['cedula'];
                $cliente->telefono = $request['telefono'];
                $cliente->email = $request['email'];
                $cliente->direccion = $request['direccion'];
                $cliente->save();
                $mensaje = 'Cliente actualizado';
            }else{
                $mensaje = 'El Numero que desea establecer se encuentra en uso';
            }
        }else{
            $cliente->nombre = $request['nombre'];
            $cliente->apellido = $request['apellido'];
            $cliente->cedula = $request['cedula'];
            $cliente->email = $request['email'];
            $cliente->direccion = $request['direccion'];
            $cliente->save();
            $mensaje = 'Cliente actualizado';
        }
        return response()->json(['Update' => $mensaje]);
    }
    
    // consulta listado de clientes para generar reporte de excel
    public function ClientesAll()
    {
        $empresas = Auth::user()->empresas;
        $clientesAll = DB::select('select * from clientes where empresas = '.$empresas);
        return response()->json($clientesAll);
    }
    
    public function ImportClientes(Request $request){
        $errores = '';
        $mensaje = '';
               // Obtener el contenido del cuerpo de la solicitud
        $json_str = $request->getContent();

        // Verificar si la cadena JSON está vacía
        if (empty($json_str)) {
            return response()->json(['error' => 'El contenido del JSON está vacío.'], 400);
        }

        // Decodificar el JSON a un array asociativo de PHP
        $datos = json_decode($json_str, true);

        // Verificar si la decodificación fue exitosa
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Manejar el error de decodificación
            return response()->json(['error' => 'Error al decodificar JSON: ' . json_last_error_msg()], 400);
        }

        // Verificar si $datos es realmente un array
        if (!is_array($datos)) {
            return response()->json(['error' => 'El JSON decodificado no es un array.'], 400);
        }
        $user = Auth::user()->id;
         $empresas = Auth::user()->empresas;
         $clientesCargados = 0;
         $clientesExistentes = [];
        foreach($datos as $cliente){
                 try {
                    $exists = cliente::where('telefono',$cliente['telefono'])->where('empresas',$empresas)->first();
                    if(!($exists) && $cliente['telefono'])
                    {
                        
                        $creado = cliente::create([
                            'nombre' => $cliente['nombre'],
                            'apellido' => 'No definido',
                            'cedula' => 'No definido',
                            'date' => Carbon::now(),
                            'telefono' => $cliente['telefono'],
                            'email' => $cliente['email'],
                            'estados'=>'1',
                            'users_id'=> $user,
                            'empresas'=>$empresas,
                            'origen' =>1
                        ]);
                        $clienteCreado = $creado->id;
                          $insert = notas::create([
                            'comentario' => 'Cliente cargado por el importador de clientes',
                            'proximo_seguimiento' => Carbon::now(),
                            'hora' => Carbon::now()->toTimeString(),
                            'estados' => 1,
                            'clientes' => $clienteCreado,
                            'users'=>$user,
                            'empresas'=>$empresas,
                        ]);
                        $clientesCargados = $clientesCargados + 1;
                        
                    }else{
                        array_push($clientesExistentes,$cliente['telefono']);
                    }
            } catch (\Throwable $th) {
                //throw $th;
                return response()->json(['error'=>'Error inesperado en el servidor: '.$th]);
            }
            $mensaje = 'numero de clientes cargados '.$clientesCargados;
        }
        return response()->json(['succes'=>$mensaje,'error'=>$clientesExistentes]);
    }
 }

