<?php

namespace App\Http\Controllers;

use DateTime;
use Carbon\Carbon;
use App\Models\setpdf;
use App\Models\cliente;
use App\Models\asesorio;
use App\Models\empresa;
use App\Models\marcas;
use App\Models\modelo;
use App\Models\pdfretoma;
use App\Models\pdfasesorios;
use App\Models\pdfdocumento;
use App\Models\pdfmatricula;
use Illuminate\Http\Request;
use App\Models\pdffinanciero;
use App\Models\vehiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Support\Facades\Storage;

class SetpdfController extends Controller
{
    public function create(Request $request)
    {
        try {
            $dt = new DateTime();
            $users = Auth::user()->id;
            $usuarionombre = Auth::user()->name;
            $usuarioemail = Auth::user()->email;
            $usuarioapellido = Auth::user()->apellido;
            $fotousuario = Auth::user()->img;
            $usuarioempresa = Auth::user()->empresas;
            $empresa = Auth::user()->empresas;
            $fecha = now();
            $nombre_empresa = empresa::find($usuarioempresa)->nombre;
            $nombre_cliente= cliente::find($request['cliente'])->nombre;
            $vehiculo = vehiculo::find($request['financiero_vehiculo_id']);//datos del vehiculo de la base de datos 
            $nombrepdf= $dt->format('Y_m_d_H_i_s').str_replace(' ','-',$nombre_cliente).'.pdf';
            $setpdf = setpdf::create(
                [
                    'clientes'=>$request['cliente'],
                    'users'=>$users,
                    'pdf'=>$nombrepdf,
                    'empresas'=>$empresa
                ]
                );
            $nombre_marca = marcas::find($vehiculo->marcas)->nombre;
            $nombre_modelo = modelo::find($vehiculo->modelos)->year;
            $asesorios = $request['financiero_asesorios'];
            $pdf = PDF::loadView('pdf',['foto1'=>$vehiculo->foto1,
                                        'foto2'=>$vehiculo->foto2,
                                        'foto3'=>$vehiculo->foto3,
                                        'foto4'=>$vehiculo->foto4,
                                        'asesorios'=>$asesorios,
                                        'placa'=>$request['financiero_vehiculo_placa'],
                                        'kilometraje'=>$request['financiero_vehiculo_kilometraje'],
                                        'valor'=>$request['financiero_vehiculo_valor'],
                                        'marca'=>$nombre_marca,
                                        'modelo'=>$nombre_modelo,
                                        // financiero 
                                        'valorfinanciar'=>$request['financiero_valor_financiamiento'],
                                        'mesesmanual'=>$request['financiero_mesesmanuales'],
                                        'cuotaextra'=>$request['financiero_couta_extra'],
                                        'tasa'=>$request['financiero_tasa_interes'],
                                        'cuarenta'=>$request['financiero_cuarentayochomeses'],
                                        'sesenta'=>$request['financiero_sesentameses'],
                                        'setenta'=>$request['financiero_setentaydosmeses'],
                                        'ochenta'=>$request['financiero_ochentameses'],
                                        'seguro'=>$request['financiero_numerodemeses_manual'],
                                        // documentos 
                                        'cedula'=>$request['documentacion_cedula'],
                                        'solicitudcredito'=>$request['documentacion_solicitud'],
                                        'certificadolaboral'=>$request['documentacion_laboral'],
                                        'extratos'=>$request['documentacion_extratos'],
                                        'declaracion'=>$request['documentacion_declaracion'],
                                        'cartascomerciales'=>$request['documentacion_cartascomerciales'],
                                        'facturaproveedor'=>$request['documentacion_proveedor'],
                                        'cartacupo'=>$request['documentacion_carta'],
                                        'camaraycomercio'=>$request['documentacion_camaradecomercio'],
                                        'rut'=>$request['documentacion_rut'],
                                        'resolucionpension'=>$request['documentacion_pension'],
                                        'desprendibles'=>$request['documentacion_desprendibles'],
                                        'certificadotradiccion'=>$request['documentacion_certificado'],
                                        //matricula
                                        'traspasos'=>$request['matricula_traspaso'],
                                        'honorarios'=>$request['matricula_honorario'],
                                        'impuestos'=>$request['matricula_impuestos'],
                                        'pignoracion'=>$request['matricula_pignoracion'],
                                        'certificadotradiccion'=>$request['matricula_certificado_tradiccion'],
                                        'siginperitaje'=>$request['matricula_cijin'],
                                        //retoma
                                        'placaretoma'=>$request['retoma_placa'],
                                        'marcaretoma'=>$request['retoma_marca'],
                                        'refvehiculo'=>$request['retoma_referencia'],
                                        'modeloretoma'=>$request['retoma_modelo'],
                                        'kilometrajeretoma'=>$request['retoma_kilometraje'],
                                        'valorretoma'=>$request['retoma_valor'],
                                        'descripcionretoma'=>$request['retoma_descripcion'],
                                        //datos del asesor
                                        'usuarioname'=>$usuarionombre,
                                        'usuarioemail'=>$usuarioemail,
                                        'apellido'=>$usuarioapellido,
                                        'fotoperfil'=>$fotousuario,
                                        'fecha'=>$fecha,
                                        'empresa'=>$nombre_empresa,
                                    ]);
            $pdf->save(public_path().'/storage/documentos/'.str_replace(' ','-',$nombrepdf))->stream('pdf');
            return response()->json(['succes'=>'Se creo de forma correcta'],200);
        } catch (\Throwable $th) {
            return response()->json(['error'=>'Se presento problema en el servidor '],500);
        }
        
    }
    public function index()
    {
        $id = $_GET['id'];
        $vista = DB::select('
        select 
        p.clientes,p.Pdf,p.id
        from 
        setpdfs p
        where p.clientes ='.$id);
        return response()->json($vista);
    }

    public function asesorios()
    {
        $vista = DB::select('select setpdf as pdf, nombre, marca,estado,valor,created_at 
        from pdfasesorios');
        return response()->json($vista);
    }

    public function pdfgenerate()
    {
        // $datos = setpdf::all();
        // // return view('pdf',compact('datos'));
        // $pdf = PDF::loadView('pdf',['datos'=>$datos]);
        // return $pdf->save(public_path().'/storage/documentos/pdf.pdf')->stream('pdf');
        $dt = new DateTime();
         
        return  $dt->format('Y_m_d_H_i_s');
    }
    public function dowload(Request $request)
    {
        // $documento = $request['documento'];
        // $paht = storage_path('app/public/documentos/'.$documento);
        // return  $paht;
    
        // return 'http://localhost/storage/documentos/'.$request['documento'];
        $documento = $request['documento'];
        $paht = storage_path('app/public/documentos/'.$documento);
        return  response()->download($paht);
    }
}
