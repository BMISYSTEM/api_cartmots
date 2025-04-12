<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permisos;
use App\Http\Requests\login;
use App\Http\Requests\users;
use App\Models\empresa;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Return_;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

use function PHPUnit\Framework\isNull;
use Illuminate\Support\Facades\Storage;

class Authcontroller extends Controller
{
    // ingreso
    public function login (login $request)
    {
             $data = $request->validated();
             //revisar password
             if (!Auth::attempt($data)) {
                return response([
                    'errors' => ['el imail o el password son incorrectos']
                ],422);
             }
             if(!Auth::user()->activo) 
             {
                 return response([
                     'errors'=>['Usuario no activo, Comuniquese con el area de soporte de cartmots.']
                     ],422);
             }
             //autenticacion con token
             $user = Auth::user();
             $paht = public_path().'/imagenes/DISTRI.png';
             //ewnvio los permisos que tiene disponible
             $permisos = Permisos::where('user_id',$user->id)->get();
             return [
                'token' => $user->createToken('token')->plainTextToken,
                'user' => $user,
                'permisos' => $permisos,
             ];
    }
    // cerrar cession
    public function logout(Request $request)
    {
      $user = $request->user();
      $user->currentAccessToken()->delete();
      return [
         'user' => null
      ];
    return $user;
   }
//    creacion de usuario nuevo
    public function create(Request $request)
    {
        // validacion de la imagen 
        $request->validate(
            [
                'imagen'    => 'required|mimes:jpg,png|max:100',
                'nombre'    => 'required',
                'apellido'  => 'required',
                'date'      => 'required',
                'cedula'    => 'required|numeric',
                'email'     => 'required|email',
                'password'  => 'required|regex:/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[@$!%*#?&])[A-Za-z0-9@$!%*#?&]+$/|min:8',
                'password_confirmation'  => 'required|same:password'
            ],
            [
                'imagen.required'               => 'la imagen es requeridaa',
                'imagen.mimes'                  => 'la imagen seleccionada no esta en un formato valido',
                'imagen.max'                    => 'tamano maximo de 100 k o 10 mb',
                'nombre.required'               => 'el nombre es requerido',
                'apellido.required'             => 'el Apellido es requerido',
                'date.required'                 => 'La fecha de nacimiento es requerido',
                'cedula.required'               => 'la cedula es requerida',
                'cedula.numeric'                => 'la cedula debe ser numerica',
                'email.required'                => 'El email es requerido',
                'email.email'                   => 'El email no es valido',
                'password.required'             => 'El password es obligatorio',
                'password_confirmation.required'=> 'Debes confirmar el password',
                'password.regex'                => 'El password debe contener una letra un numero y un signo especial y minimo 8 carcteres',
                'password_confirmation.same'    => 'La contrase no coinciden',
            ]
        );
        
        //validar numero de usuarios 
        $empresa = Auth::user()->empresas;
        $nomempresa = empresa::find($empresa);
        $numeroUser = DB::select('select count(*) cantidad from users where empresas ='.$empresa);
        // guardo la imagen en la carpeta
        if(!($numeroUser[0]->cantidad >= 8) ){
            
            $fotoPerfil = $request ->file('imagen')->store('public/'.$nomempresa['nombre'].'/logos');
            $fotoPerfilUrl = Storage::url($fotoPerfil);
            // inserts----------------------------------------------------------------------------
            $user= User::create([
                'name' =>$request['nombre'],
                'apellido' =>$request['apellido'],
                'email' =>$request['email'],
                'password' =>bcrypt($request['password']),
                'cedula' =>$request['cedula'],
                'empresas' =>$empresa,
                'img' =>$fotoPerfilUrl,
                'rol'=>'0'
            ]);
            $permisos = Permisos::create([
                'dashboard'=>($request['dashboard']), 
                'administrador'=>($request['administrador']),
                'usuarios'=>($request['usuarios']),
                'recepcion'=>($request['recepcion']),
                'ajustes'=>($request['ajustes']),
                'campanas'=>($request['campanas']),
                'contabilidad'=>($request['contabilidad']),
                'transferencias'=>($request['transferencias']),
                'proveedor'=>($request['proveedor']),
                'user_id'=>$user->id
            ]);
            return ['mensaje'=>"se creo el usuario"];   
        }else{
            return ['mensaje'=>"Ya a alcanzado el numero limite de usuarios 8"]; 
        }
    }
//   optener los permisos de un usuario
    public function permisos(Request $request)
    {
        $user = $request->user();
        $permisos = Permisos::where('user_id',$user->id)->get();
        return $permisos;
    }
    // creacion rapida de un usuario
    public function force(){
        $user = User::create([
            'name'=> 'Administrador',
            'apellido'=>'Admin',
            'email'=> 'Administrador@administrador.com',
            'password'=>bcrypt('12345'),
            'empresas'=>'1',
            'cedula'=>'1143994831',
            'img'=>'sinfoto.jpg'
         ]);
         //creando el token para utenticar
         $user->createToken('token')->plainTextToken;
         return response()->json(['usuarios'=>User::all()]);
    }
    public function index()
    {
        $empresas = Auth::user()->empresas;
        $vista = DB::select(
        "select u.id,u.name,u.apellido,u.email,u.cedula,u.created_at as fecha_inicio,count(c.id) as clientes,
        count(
        case
        when c.estados = 7 then 1 end) as cerrados from users u 
        inner join clientes c on c.users_id = u.id
        inner join estados e on c.estados = e.id
        where u.empresas = ".$empresas."   
        GROUP BY u.name,u.email,u.cedula,u.created_at,u.id,u.apellido");
        return response()->json($vista);
    }
    // muestra los usuarios de esa empresa con sus permisos respectivos
    public function users_permisos()
    {
        $empresas = Auth::user()->empresas;
        $vista = DB::select(
        "   select 
            u.id,u.activo,u.name,u.apellido,u.cedula,u.email,u.img, p.dashboard,p.costos,
            u.created_at,u.rol,
            p.administrador, p.usuarios, p.recepcion, p.ajustes, 
            p.campanas, p.contabilidad, p.transferencias, p.proveedor
            from users u
            inner join permisos p on p.user_id = u.id
            inner join empresas em on u.empresas = em.id
            where em.id = '".$empresas."'
        ");
        return response()->json($vista);
    }
    //actualiza los permisos de los usuarios
    public function updatePermisos(Request $request)
    {
        $permisos = Permisos::where('user_id',$request['id'])->get();
        $permisos->toQuery()->update([
            'dashboard' => $request['dashboard'],
            'administrador' => $request['administrador'],
            'recepcion' => $request['recepcion'],
            'ajustes' => $request['ajustes'],
            'campanas' => $request['campanas'],
            'contabilidad' => $request['contabilidad'],
            'transferencias' => $request['transferencias'],
            'proveedor' => $request['proveedor'],
            'costos'=>$request['costos']
           ]
        );
        return 'Actualizado...';
    }
    //bloquea a los usuarios
    public function BloqueoUser(Request $request)
    {
        $permisos = User::where('id',$request['id'])->get();
        $permisos->toQuery()->update([
            'activo' => 0,
           ]
        );
        return 'Actualizado...';
    }
    //activasion de usuarios
    public function ActivaUser(Request $request)
    {
        $permisos = User::where('id',$request['id'])->get();
        $permisos->toQuery()->update([
            'activo' => 1,
           ]
        );
        return 'Actualizado...';
    }
    // consulta el rol del usuario
    public function user()
    {
        return response()->json(Auth::user()->rol);
    }
    // consulta el codigo de verificacion de registro de empresa
    public function codigoempresa(Request $request){
        $codigo = $request['codigo'];
        $verificado = DB::select("SELECT * FROM empresas WHERE token = '". $codigo ."'
                                    AND confirmado = 0");
        return response()->json($verificado);
    }
    // registramos la empresa
    public function registroempresa(Request $request)
    {
        // creacion de dircetorios 
        Storage::makeDirectory('./public/'.$request['empresa']);
        Storage::makeDirectory('./public/'.$request['empresa'].'/logos');
        Storage::makeDirectory('./public/'.$request['empresa'].'/asesorios');
        Storage::makeDirectory('./public/'.$request['empresa'].'/docpdf');
        Storage::makeDirectory('./public/'.$request['empresa'].'/documentos');
        Storage::makeDirectory('./public/'.$request['empresa'].'/peritaje');
        Storage::makeDirectory('./public/'.$request['empresa'].'/vehiculos');
        // mover el archivo a la carpeta
        $logo = $request->file('logo')->store('public/'.$request['empresa'].'/logos');
        $urllogo = Storage::url($logo);
        // registro de empresa
        $empresa = empresa::find($request['id']);
        $empresa->nombre = $request['empresa'];
        $empresa->registrados = 1;
        $empresa->logo = $urllogo;
        $empresa->save();
        // creacion del usuario 
        $user= User::create([
            'name' =>$request['usuario'],
            'apellido' =>'No definido',
            'email' =>$request['usuario'].$request['empresa'].'@gmail.com',
            'password' =>bcrypt($request['password']),
            'cedula' =>$request['empresa'],
            'empresas' =>$request['id'],
            'img' => $urllogo,
            'rol'=>'1'
        ]);
        $permisos = Permisos::create([
            'dashboard'=>1, 
            'administrador'=>1,
            'usuarios'=>1,
            'recepcion'=>1,
            'ajustes'=>1,
            'campanas'=>1,
            'contabilidad'=>1,
            'transferencias'=>1,
            'proveedor'=>1,
            'user_id'=>$user->id
        ]);
        return response()->json($empresa);
    }

    // actualiza el nombre del usuario 
    public function updatenombre (Request $request)
    {
        $request->validate(
            [
            'nombre'=>'required'
            ],
            [
            'nombre.required'=>'El nombre es obligatorio'
            ]
        );
        $id = Auth::user()->id;
        $datauser = User::find($id);
        $datauser->name = $request['nombre'];
        $datauser->save();
        return response()->json(['mensaje'=>'Nombre actualizado con exito']);
    }
    public function updateapellido (Request $request)
    {
        $request->validate(
            [
            'apellido'=>'required'
            ],
            [
            'apellido.required'=>'El apellido es obligatorio'
            ]
        );
        $id = Auth::user()->id;
        $datauser = User::find($id);
        $datauser->apellido = $request['apellido'];
        $datauser->save();
        return response()->json(['mensaje'=>'Apellido actualizado con exito']);
    }
    public function updateemail (Request $request)
    {
        $request->validate(
            [
            'email'=>'required'
            ],
            [
            'email.required'=>'El email es obligatorio'
            ]
        );
        $id = Auth::user()->id;
        $datauser = User::find($id);
        $datauser->email = $request['email'];
        $datauser->save();
        return response()->json(['mensaje'=>'Email actualizado con exito']);
    }
    public function updatecedula (Request $request)
    {
        $request->validate(
            [
            'cedula'=>'required'
            ],
            [
            'cedula.required'=>'El email es obligatorio'
            ]
        );
        $id = Auth::user()->id;
        $datauser = User::find($id);
        $datauser->cedula = $request['cedula'];
        $datauser->save();
        return response()->json(['mensaje'=>'Cedula actualizado con exito']);
    }
    public function updatepassword (Request $request)
    {
        $request->validate(
            [
            'password'=>'required|regex:/^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[@$!%*#?&])[A-Za-z0-9@$!%*#?&]+$/|min:8'
            ],
            [
            'password.required'=>'El Password es obligatorio',
            'password.regex'=>'El passwor debe contener un numero, una letra y un caracter especial',
            'password.min'=>'El passwor debe contener minimo 8 caracteres',
            ]
        );
        $id = Auth::user()->id;
        $datauser = User::find($id);
        $datauser->password = bcrypt($request['password']);
        $datauser->save();
        return response()->json(['mensaje'=>'Password actualizado con exito']);
    }
    public function updateFoto (Request $request) 
    {       
            $mensaje = '';
            $request->validate(
                [
                    'imagen'=>'required|mimes:jpg,png|max:1000'
                ],
                [
                    'imagen.required' => 'la imagen es requerida',
                    'imagen.mimes' => 'La imagen debe ser en formato jpg',
                    'imagen.max' => 'la imagen supera el tamno especificado de 10 mb o 100kb',
                ]
            );
            // validamos si existe el archivo
            $id = Auth::user()->id;
            $fotouser = Auth::user()->img;
            $nomfoto =  explode('/logos', $fotouser);
            $empresa = Auth::user()->empresas;
            $nombreempresa = empresa::find ($empresa);
            $imagendelete = public_path($nombreempresa . '/logos/'. $nomfoto[0]);
            if (File::exists($imagendelete)) 
            {
                File::delete($imagendelete);
            }
            $fotoPerfil = $request ->file('imagen')->store('public/'. $nombreempresa['nombre'] .'/logos/');
            $fotoPerfilUrl = Storage::url($fotoPerfil);
            $user = User::find($id);
            $user->img = $fotoPerfilUrl;
            $user->save();
            return response()->json(['mensaje'=>'Se actualizo la imagen ']);
    }
}
