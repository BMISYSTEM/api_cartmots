<?php

namespace App\Http\Controllers;

use App\Models\cliente;
use App\Models\datos_credito;
use App\Models\informacion_laborale;
use App\Models\ingresos_egreso;
use App\Models\pdffinanciero;
use App\Models\pdfsolicitud;
use App\Models\pdfsolicitude;
use App\Models\referencia;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SolicitudCredito extends Controller
{
    public function index()
    {   
        $cliente = $_GET['id'];
        $consulta = DB::select('select * from pdfsolicitudes where clientes ='.$cliente);
        return response()->json($consulta);
    }
    public function create(Request $request)
    {
        $dt = new DateTime();
        $users = Auth::user()->id;
        $usuarionombre = Auth::user()->name;
        $usuarioemail = Auth::user()->email;
        $usuarioapellido = Auth::user()->apellido;
        $usuariocedula = Auth::user()->cedula;
        $fotousuario = Auth::user()->img;
        $usuarioempresa = Auth::user()->empresa;
        $empresa = Auth::user()->empresas;
        $telefono= cliente::find($request['cliente'])->celular;
        $fecha = now();
        $nombrepdf= $dt->format('Y_m_d_H_i_s').str_replace(' ','-','Solicitud_credito_'.$telefono).'.pdf';
        
        $pdfsolicitud = pdfsolicitude::create([
            'clientes'=>$request['cliente'],
            'nombre'=>$nombrepdf,
            'users'=>$users,
            'empresas'=>$empresa
        ]);

        $datos_del_credito = datos_credito::create([
            'clientes'=>$request['cliente'],
            'marca'=>$request['marca'],
            'vehiculo'=>$request['vehiculo'],
            'valor_seguro'=>$request['valorseguro'],
            'plan'=>$request['plan'],
            'valor_vehiculo'=>$request['valorvehiculo'],
            'valor_cuota_extra'=>$request['valorcuotaextra'],
            'valor_financiar'=>$request['valorfinanciar'],
            'tipo'=>$request['tipo'],
            'cuota_inicial'=>$request['cuotainicial'],
            'plazo'=>$request['plazo'],
            'tasa'=>$request['tasa'],
            'financiera'=>$request['Financiera'],
            'pdf'=>$pdfsolicitud['id'],
            'empresas'=>$empresa
        ]);
        // actualiza los datos del cliente
        //condicionar a que si no vienen estos datos sacar error y no dejar generar el pdf
        // $cliente = cliente::find($request['cliente']);
        //     $cliente->nombre = $request['nombre'];
        //     $cliente->apellido = $request['apellido'];
        //     $cliente->cedula = $request['numerodoc'];
        //     $cliente->date = $request['fechanacimiento'];
        //     $cliente->telefono = $request['telefono'];
        //     $cliente->email = $request['email'];
        //     $cliente->TIPO_DOCUMENTO = $request['tipodedocumento'];
        //     $cliente->FECHA_EXPEDICCION = $request['fechaespediccion'];
        //     $cliente->ciudad = $request['ciudad'];
        //     $cliente->genero = $request['genero'];
        //     $cliente->estado_civil = $request['estadocivil'];
        //     $cliente->direccion = $request['direccion'];
        //     $cliente->celular = $request['celular'];
        //     $cliente->tipo_vivienda = $request['tipodevivienda'];
        //     $cliente->antiguedad_vivienda = $request['antiguedadvivienda'];
        //     $cliente->save();
        // gusardar la informacion laboral

        $informacion_laboral = informacion_laborale::create([
            'clientes'=>$request['cliente'],
            'contrato'=>$request['contrato'],
            'empresa'=>$request['empresa'],
            'direccion'=>$request['direccion'],
            'nit'=>$request['nit'],
            'profecion'=>$request['profesion'],
            'telefono'=>$request['telefono'],
            'antiguedad'=>$request['antiguedad'],
            'pdf'=>$pdfsolicitud['id'],
            'empresas'=>$empresa
        ]);

        // creacion de ingreso y egreso
        $ingresos_egresos = ingresos_egreso::create([
            'clientes'=>$request['cliente'],
            'ingresos_fijos'=>$request['ingresofijo'],
            'gastos'=>$request['gastos'],
            'total_ingresos'=>$request['totalingreso'],
            'ingreso_variable'=>$request['ingresovariable'],
            'otros_egresos'=>$request['otrosegresos'],
            'total_activos'=>$request['totalactivos'],
            'otros_ingresos'=>$request['otrosingresos'],
            'tiene_vehiculo'=>$request['tienevehiculo'],
            'total_pasivos'=>$request['totalpasivos'],
            'pdf'=>$pdfsolicitud['id'],
            'empresas'=>$empresa
        ]);

        // Referencias

        $referencias = referencia::create([
            'clientes'=>$request['cliente'],
            'personal1_nombre'=>$request['ref1nombre'],
            'personal1_ciudad'=>$request['ref1ciudad'],
            'personal1_telefono'=>$request['ref1telefono'],
            'personal1_direccion'=>$request['ref1direccion'],
            'personal2_nombre'=>$request['ref2nombre'],
            'personal2_ciudad'=>$request['ref2ciudad'],
            'personal2_telefono'=>$request['ref2telefono'],
            'personal2_direccion'=>$request['ref2direccion'],
            'familiares1_nombre'=>$request['familiar1nombre'],
            'familiares1_ciudad'=>$request['familiar1ciudad'],
            'familiares1_telefono'=>$request['familiar1telefono'],
            'familiares1_direccion'=>$request['familiar1direccion'],
            'familiares2_nombre'=>$request['familiar2nombre'],
            'familiares2_ciudad'=>$request['familiar2ciudad'],
            'familiares2_telefono'=>$request['familiar2telefono'],
            'familiares2_direccion'=>$request['familiar2direccion'],
            'empresariales1_nombre'=>$request['empresarial1nombre'],
            'empresariales1_ciudad'=>$request['empresarial1ciudad'],
            'empresariales1_nit'=>$request['empresarial1nit'],
            'empresariales1_direccion'=>$request['empresarial1direccion'],
            'empresariales1_telefono'=>$request['empresarial1telefono'],
            'empresariales2_nombre'=>$request['empresarial2nombre'],
            'empresariales2_ciudad'=>$request['empresarial2ciudad'],
            'empresariales2_nit'=>$request['empresarial2nit'],
            'empresariales2_direccion'=>$request['empresarial2direccion'],
            'empresariales2_telefono'=>$request['empresarial2telefono'],
            'conyuge_nombre'=>$request['conyugenombre'],
            'conyuge_fechanacimiento'=>$request['conyugefecha'],
            'conyuge_cedula'=>$request['conyugecedula'],
            'conyuge_empresa'=>$request['conyugeemporesa'],
            'conyuge_telefono'=>$request['conyugetelefono'],
            'coyuge_salario'=>$request['conyugesalario'],
            'conyuge_otros_ingreso'=>$request['conyugeotrosingresos'],
            'conyuge_egresos'=>$request['conyugeegresos'],
            'pdf'=>$pdfsolicitud['id'],
            'empresas'=>$empresa
        ]);
            
        // CREA EL PDF

        $pdf = Pdf::loadView('solicitud', [
            'usuario'=>$usuarionombre .' '.$usuarioapellido,
            'cedulausuario'=>$usuariocedula,
            'fecha'=>$fecha,
            'cliente'=>$request['cliente'],
            'marca'=>$request['marca'],
            'vehiculo'=>$request['vehiculo'],
            'valorseguro'=>$request['valorseguro'],
            'valorvehiculo'=>$request['valorvehiculo'],
            'valorcuotaextra'=>$request['valorcuotaextra'],
            'valorfinanciar'=>$request['valorfinanciar'],
            'cuotainicial'=>$request['cuotainicial'],
            'plazo'=>$request['plazo'],
            'tipo'=>$request['tipo'],
            'plan'=>$request['plan'],
            'tasa'=>$request['tasa'],
            'Financiera'=>$request['Financiera'],
            'nombre'=>$request['nombre'],
            'genero'=>$request['genero'],
            'apellido'=>$request['apellido'],
            'estadocivil'=>$request['estadocivil'],
            'fechanacimiento'=>$request['fechanacimiento'],
            'direccion'=>$request['direccion'],
            'tipodedocumento'=>$request['tipodedocumento'],
            'telefono'=>$request['telefono'],
            'numerodoc'=>$request['numerodoc'],
            'celular'=>$request['celular'],
            'fechaespediccion'=>$request['fechaespediccion'],
            'email'=>$request['email'],
            'ciudad'=>$request['ciudad'],
            'tipodevivienda'=>$request['tipodevivienda'],
            'antiguedadvivienda'=>$request['antiguedadvivienda'],
            'contrato'=>$request['contrato'],
            'direccion'=>$request['direccion'],
            'empresa'=>$request['empresa'],
            'nit'=>$request['nit'],
            'profesion'=>$request['profesion'],
            'telefono'=>$request['telefono'],
            'antiguedad'=>$request['antiguedad'],
            'ingresofijo'=>$request['ingresofijo'],
            'gastos'=>$request['gastos'],
            'totalingreso'=>$request['totalingreso'],
            'ingresovariable'=>$request['ingresovariable'],
            'otrosegresos'=>$request['otrosegresos'],
            'totalactivos'=>$request['totalactivos'],
            'otrosingresos'=>$request['otrosingresos'],
            'tienevehiculo'=>$request['tienevehiculo'],
            'totalpasivos'=>$request['totalpasivos'],
            'ref1nombre'=>$request['ref1nombre'],
            'ref1ciudad'=>$request['ref1ciudad'],
            'ref1telefono'=>$request['ref1telefono'],
            'ref1direccion'=>$request['ref1direccion'],
            'ref2nombre'=>$request['ref2nombre'],
            'ref2ciudad'=>$request['ref2ciudad'],
            'ref2telefono'=>$request['ref2telefono'],
            'ref2direccion'=>$request['ref2direccion'],
            'familiar1nombre'=>$request['familiar1nombre'],
            'familiar1ciudad'=>$request['familiar1ciudad'],
            'familiar1telefono'=>$request['familiar1telefono'],
            'familiar1direccion'=>$request['familiar1direccion'],
            'familiar2nombre'=>$request['familiar2nombre'],
            'familiar2ciudad'=>$request['familiar2ciudad'],
            'familiar2telefono'=>$request['familiar2telefono'],
            'familiar2direccion'=>$request['familiar2direccion'],
            'empresarial1nombre'=>$request['empresarial1nombre'],
            'empresarial1ciudad'=>$request['empresarial1ciudad'],
            'empresarial1nit'=>$request['empresarial1nit'],
            'empresarial1direccion'=>$request['empresarial1direccion'],
            'empresarial1telefono'=>$request['empresarial1telefono'],
            'empresarial2nombre'=>$request['empresarial2nombre'],
            'empresarial2ciudad'=>$request['empresarial2ciudad'],
            'empresarial2nit'=>$request['empresarial2nit'],
            'empresarial2direccion'=>$request['empresarial2direccion'],
            'empresarial2telefono'=>$request['empresarial2telefono'],
            'conyugenombre'=>$request['conyugenombre'],
            'conyugefecha'=>$request['conyugefecha'],
            'conyugecedula'=>$request['conyugecedula'],
            'conyugeemporesa'=>$request['conyugeemporesa'],
            'conyugetelefono'=>$request['conyugetelefono'],
            'conyugesalario'=>$request['conyugesalario'],
            'conyugeotrosingresos'=>$request['conyugeotrosingresos'],
            'conyugeegresos'=>$request['conyugeegresos']


        ]);
        $pdf->save(public_path().'/storage/documentos/'.str_replace(' ','-',$nombrepdf))->stream('solicitud');
        return 'Solicitud Creado';
    }
    public function view()
    {
        $pdf = Pdf::loadView('solicitud', []);
        // $pdf->loadHTML('<h1>Test</h1>');
        // return $pdf->save(public_path().'/storage/documentos/'.str_replace(' ','-','000001solicitud.pdf'))->stream('solicitud');
        return $pdf->stream();
    }

    public function datosCredito(Request $request)
    {
        $empresa = Auth::user()->empresas;
        $cliente = $request->query('cliente');
        if($cliente == null){
            return response()->json(['error'=>'No se encontro el cliente'],404);
        }
        $datos = DB::select('select * from datos_creditos where empresas = '.$empresa.' and clientes = '.$cliente.'  order by created_at DESC LIMIT 1');
        return response()->json($datos);
    }
}
