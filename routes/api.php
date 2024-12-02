<?php

use App\Http\Controller\Wpp\WppController;
use Illuminate\Support\Facades\Http;


use App\Http\Controllers\Actividades\ActividadesController;
use App\Http\Controllers\AsesorioController;
use App\Http\Controllers\Asociaciones\Controller\AsociacionesController;
use App\Http\Controllers\Authcontroller;
use App\Http\Controllers\botController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\GeneradorReportes;
use App\Http\Controllers\dashboard;
use App\Http\Controllers\Empresas\Controller\EmpresaController;
use App\Http\Controllers\EstadoController;
use App\Http\Controllers\graficos;
use App\Http\Controllers\ImagenesController;
use App\Http\Controllers\Logistica\LogisticaController;
use App\Http\Controllers\MarcasController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\Motivos\MotivosController;
use App\Http\Controllers\NotasController;
use App\Http\Controllers\NotificacionController;
use App\Http\Controllers\PasarelaController;
use App\Http\Controllers\Proveedor\ProveedorController;
use App\Http\Controllers\ResultadoController;
use App\Http\Controllers\SetpdfController;
use App\Http\Controllers\SolicitudCredito;
use App\Http\Controllers\TransferenciasController;
// use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\Vehiculos\Controller\VehiculoController;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

Route::middleware('auth:sanctum')->group(function(){
    // usuario
    Route::get('/users', function (Request $request) {
        return $request->user();
    });
    Route::post('/users/updatefoto',[Authcontroller::class,'updateFoto']);
    Route::post('/users/updatenombre',[Authcontroller::class,'updatenombre']);
    Route::post('/users/updateapellido',[Authcontroller::class,'updateapellido']);
    Route::post('/users/updateemail',[Authcontroller::class,'updateemail']);
    Route::post('/users/updatecedula',[Authcontroller::class,'updatecedula']);
    Route::post('/users/updatepassword',[Authcontroller::class,'updatepassword']);
    // dashboard
    Route::get('/dashboard/resumen',[dashboard::class,'index']);
    // notificaciones
    Route::get('/clientes/notificaciones',[NotificacionController::class,'index']);
    Route::get('/clientes/alertas',[NotificacionController::class,'alertas']);

    Route::post('/create',[Authcontroller::class,'create']);
    Route::post('/logout',[Authcontroller::class,'logout']);
    Route::get('/permisos',[Authcontroller::class,'permisos']);
    //marcas
    Route::post('/marca',[MarcasController::class,'create']);
    Route::get('/index',[MarcasController::class,'index']);
    // update marcas
    Route::post('/marca/update',[MarcasController::class,'update']);
    //modelos
    Route::post('/modelo',[ModeloController::class,'create']);
    Route::get('/modelo',[ModeloController::class,'index']);
    //estados
    Route::post('/estados',[EstadoController::class,'create']);
    Route::post('/estados/update',[EstadoController::class,'update']);
    Route::get('/estados',[EstadoController::class,'index']);
     /*
    |--------------------------------------------------------------------------
    | clientes
    |--------------------------------------------------------------------------
    |
    | create,index
    |
    */
    Route::post('/clientes',[ClienteController::class,'create']);
    Route::get('/clientes',[ClienteController::class,'index']);
    Route::get('/listcliente',[ClienteController::class,'ClientesAll']);
    Route::get('/clientes/infocliente',[ClienteController::class,'infocliente']);
    Route::get('/clientes/pendientes',[ClienteController::class,'pendientes']);
    Route::get('/clientes/aprobados',[ClienteController::class,'aprobados']);
    Route::get('/clientes/vendidos',[ClienteController::class,'vendidos']);
    Route::post('/clientes/documentos',[ClienteController::class,'descargasdoc']);
    Route::post('/clientes/estados',[ClienteController::class,'updateEstados']);
    Route::get('/clientes/seguimiento',[ClienteController::class,'seguimiento']);
    Route::get('/clientes/finalizados',[ClienteController::class,'finalizados']);
    Route::get('/clientes/busqueda',[ClienteController::class,'busqueda']);
    Route::post('/cliente/documento',[ClienteController::class,'documentos']);
    Route::get('/clientes/documentos/index',[ClienteController::class,'documentosall']);
    Route::get('/clientes/documentos/centrofinanciero',[ClienteController::class,'seguimiento_centro']);
    Route::get('/cliente',[ClienteController::class,'cliente']);
    Route::post('//carga-cliente',[ClienteController::class,'ImportClientes']);
    Route::post('clientes/update',[ClienteController::class,'updateCliente']);
    // graficos
    Route::get('/clientes/ventas/barra',[graficos::class,'barra']);

    //todos los usuarios\
    Route::get('/usuarios',[Authcontroller::class,'index']);
    Route::get('/usuarios/permisos',[Authcontroller::class,'users_permisos']);
    Route::post('/usuarios/update_permisos',[Authcontroller::class,'updatePermisos']);
    Route::post('/usuarios/bloqueo',[Authcontroller::class,'BloqueoUser']);
    Route::post('/usuarios/ativacion',[Authcontroller::class,'ActivaUser']);
    //asesorios
    Route::post('/asesorios',[AsesorioController::class,'create']);
    Route::get('/asesorios',[AsesorioController::class,'index']);
    //seguimiento
    Route::post('/seguimiento/nota',[NotasController::class,'create']);
    Route::get('/seguimiento/nota',[NotasController::class,'index']);

    // Informacion para generacion de pdf pedido
    Route::post('/pdfpedido',[NotasController::class,'findPedido']);
    //resultados
    Route::post('/resultado',[ResultadoController::class,'create']);
    Route::get('/resultado',[ResultadoController::class,'index']);
    //pdfs cotizacion
    Route::post('/setpedf',[SetpdfController::class,'create']);
    Route::get('/setpedf',[SetpdfController::class,'index']);
    Route::get('/setpedf/asesorio',[SetpdfController::class,'asesorios']);
    Route::post('/pdf/descarga',[SetpdfController::class,'dowload']);
    //pdf solicitud de credito
    Route::post('/solicitud/credito',[SolicitudCredito::class,'create']);
    Route::get('/solicitud/index',[SolicitudCredito::class,'index']);
    //roles
    Route::get('/users/rol',[Authcontroller::class,'user']);
    // intercompany
    Route::post('/intercompany',[TransferenciasController::class,'create']);
    Route::post('/intercompany/asignar',[TransferenciasController::class,'asignar']);
    Route::get('/intercompany/enviados',[TransferenciasController::class,'index']);
    Route::get('/intercompany/recepcion',[TransferenciasController::class,'recepcionenviados']);

    // bot
    Route::post('/bot/newchat',[botController::class,'createNewBot']);
    Route::post('/bot/updatecampana',[botController::class,'updatecampana']);
    Route::post('/bot/asignacion',[botController::class,'createclientebot']);
    Route::get('/bot/newchat',[botController::class,'index']);
    Route::get('/bot/asesores',[botController::class,'asesores']);
    Route::get('/bot/campana',[botController::class,'listchat']);
    Route::get('/bot/campanas/seguimiento',[botController::class,'campanaseguimiento']);
    Route::get('/bot/campanas/chat',[botController::class,'chatbotone']);
    Route::get('/bot/findcampana',[botController::class,'findcampana']);
    Route::get('/bot/mensajes',[botController::class,'mensajes']);

     /*
    |--------------------------------------------------------------------------
    | Rutas de vehiculos
    |--------------------------------------------------------------------------
    |
    | create, update , delete , find , all, metodos que se consumen con las rutas.
    |
    */
    Route::post('/createvehiculo',[VehiculoController::class,'createVehiculo']);
    Route::post('/updatevehiculo',[VehiculoController::class,'updateVehiculo']);
    Route::get('/deletevehiculo',[VehiculoController::class,'deleteVehiculo']);
    Route::get('/allvehiculo',[VehiculoController::class,'allVehiculos']);
    Route::get('/findvehiculo',[VehiculoController::class,'findVehiculo']);
    Route::get('/indexintercompany',[VehiculoController::class,'indexIntercompany']);
    Route::post('/updateimagen',[VehiculoController::class,'updateImagenVehiculo']);
    Route::post('/agotarvehiculo',[VehiculoController::class,'agotarVehiculo']);
    Route::post('/indexvehiculosproveedor',[VehiculoController::class,'indexVehiculosProveedor']);

     /*
    |--------------------------------------------------------------------------
    | Tienen que ver con la empresas
    |--------------------------------------------------------------------------
    |
    | index
    |
    */
    Route::get('/empresas',[EmpresaController::class,'indexEmpresas']);
    /*
    |--------------------------------------------------------------------------
    | asociaciones
    |--------------------------------------------------------------------------
    |
    | create,consulta todas las solicitudes
    |
    */
    Route::post('/newasociacion',[AsociacionesController::class,'createAsociacion']);
    Route::post('/updatesolicitud',[AsociacionesController::class,'updateSolicitud']);
    Route::get('/indexsolicitudes',[AsociacionesController::class,'indexsolicitudes']);
    Route::get('/indexsolicitudesrecibidas',[AsociacionesController::class,'indexsolicitudesRecibidas']);
    Route::get('/indexasociaciones',[AsociacionesController::class,'indexasociaciones']);
    Route::get('/cancelarenviosolicitud',[AsociacionesController::class,'cancelarEnvioSolicitud']);
    Route::get('/aprobarsolicitud',[AsociacionesController::class,'AprobarSolicitud']);
     /*
    |--------------------------------------------------------------------------
    | Actividades
    |--------------------------------------------------------------------------
    |
    | create,consulta todas las actividades
    |
    */
    Route::post('/createactividad',[ActividadesController::class,'createActividad']);
    Route::post('/updateactividad',[ActividadesController::class,'updateActividad']);
    Route::post('/deleteactividad',[ActividadesController::class,'deleteActividad']);
    Route::post('/findactividad',[ActividadesController::class,'findActividad']);
    Route::get('/allactividades',[ActividadesController::class,'allActividad']);
     /*
    |--------------------------------------------------------------------------
    | Motivos
    |--------------------------------------------------------------------------
    |
    | create,consulta todas las motivos
    |
    */
    Route::post('/createmotivos',[MotivosController::class,'createMotivo']);
    Route::post('/updatemotivos',[MotivosController::class,'updateMotivo']);
    Route::post('/deletemotivos',[MotivosController::class,'deleteMotivo']);
    Route::post('/findmotivos',[MotivosController::class,'findMotivo']);
    Route::get('/allmotivos',[MotivosController::class,'allMotivo']);
  /*
    |--------------------------------------------------------------------------
    | Logistica / movimientos
    |--------------------------------------------------------------------------
    |
    | create,consulta todas las movimientos
    |
    */
    Route::post('/createmovimiento',[LogisticaController::class,'createMovimiento']);
    Route::post('/updatemovimiento',[LogisticaController::class,'updateMovimiento']);
    Route::post('/deletemovimiento',[LogisticaController::class,'deleteMovimiento']);
    Route::post('/findmovimiento',[LogisticaController::class,'findMovimiento']);
    Route::get('/allmovimiento',[LogisticaController::class,'allMovimientos']);
     /*
    |--------------------------------------------------------------------------
    | costos
    |--------------------------------------------------------------------------
    |
    | Caso de uso
    |
    */
    Route::post('/costovehiculo',[LogisticaController::class,'costosVehiculo']);
    Route::post('/costoproveedor',[LogisticaController::class,'costoProveedor']);
    Route::post('/costoscliente',[LogisticaController::class,'costosCliente']);
    Route::get('/allnegociosvehiculos',[LogisticaController::class,'allnegociosvehiculos']);
    /*
    |--------------------------------------------------------------------------
    | proveedor
    |--------------------------------------------------------------------------
    |
    | create,consulta todas las movimientos
    |
    */
    Route::post('/createproveedor',[ProveedorController::class,'createProveedor']);
    Route::post('/updateproveedor',[ProveedorController::class,'updateProveedor']);
    Route::post('/deleteproveedor',[ProveedorController::class,'deleteProveedor']);
    Route::post('/findproveedor',[ProveedorController::class,'findProveedor']);
    Route::get('/allproveedor',[ProveedorController::class,'allProveedor']);

    // pago de mercado pago

    Route::post('/preferences',[PasarelaController::class,'preferences']);



    // imagenes
    Route::get('/imagenes',[ImagenesController::class,'store']);

    //configuraciones
    Route::get('/configuraciones',function(){
        $empresa = Auth::user()->empresas;

        $modulo_costo = DB::select("SELECT modulo_costos from configuraciones where empresa_id = ".$empresa);
        return response()->json($modulo_costo);
    });

     /*
    |--------------------------------------------------------------------------
    | Generador de reporte
    |--------------------------------------------------------------------------
    |
    | consulta tipo post
    |
    */
    Route::post('/syprodreport',[GeneradorReportes::class,'ConsultaSQL']);
     /*
    |--------------------------------------------------------------------------
    | rutas negocio
    |--------------------------------------------------------------------------
    |
    | elimina el negocio
    |
    */
    Route::get('/allnegocios',[LogisticaController::class,'allnegocios']);
    Route::post('/seguimiento/deletenegocio',[NotasController::class,'deleteNegocioId']);
    Route::post('/seguimiento/cerrarnegocio',[NotasController::class,'closeNegocio']);
  });





Route::get('/notificacionpago',[PasarelaController::class,'notificacionPago']);
Route::post('/codigo/empresa',[Authcontroller::class,'codigoempresa']);
Route::post('/empresa/registro',[Authcontroller::class,'registroempresa']);
Route::post('/login',[Authcontroller::class,'login']);
Route::get('/force',[Authcontroller::class,'force']);

//generar link
Route::get('/link',function(){
    Artisan::call('storage:link');
    Artisan::call('optimize');
    return response()->json(['succes'=>'Rutas actualizadas, se creo el storage Link']);
});
Route::get('/prueba',[ClienteController::class,'index']);
// conversacion con el bot
Route::get('/bot/chat',[botController::class,'find']);
Route::get('/bot/chat/consulta',[botController::class,'consulta']);
Route::post('/bot/chat/save',[botController::class,'savechat']);
// pasarela de pagos

Route::get('/mediospagos',function(){
    $response = Http::withHeaders([
    'Authorization' => 'Bearer TEST-1085150894423410-030803-ea4be12ca2083d3a93f496874831507f-507411332'
    ])->get('https://api.mercadopago.com/v1/payment_methods');

    return $response->json();
});

Route::post('/wpp',[WppController::class,'wppPost']);
Route::get('/wpp',[WppController::class,'wppGet']);

Route::get('/realizarpago',function(){
    MercadoPagoConfig::setAccessToken("ACCESS_TOKEN");

    $response = Http::withHeaders([
    'Authorization' => 'Bearer TEST-1085150894423410-030803-ea4be12ca2083d3a93f496874831507f-507411332',
    'X-Idempotency-Key: 0d5020ed-1af6-469c-ae06-c3bec19954bb'
    ])->post(    'https://api.mercadopago.com/v1/payments');


$createRequest = [
    "additional_info" => [
        "items" => [
            [
                "id" => "plan_1",//Es el identificador del anuncio del producto adquirido
                "title" => "Plan Mensual",//titulo del producto
                "description" => "Plan Mensual",//descripcion del producto
                // "picture_url" => "https://http2.mlstatic.com/resources/frontend/statics/growth-sellers-landings/device-mlb-point-i_medium2x.png",
                "category_id" => "Plan",//categoria del producto
                "quantity" => 1,//cantidad de productos
                "unit_price" => 25000000,//precio unitario
                "type" => "plan",//tipo de producto
                "event_date" => "2024-06-30T09:37:52.000-04:00",//fecha del pago
                "warranty" => false,//sin garantia
                // si es un viaje aca va su info
                "category_descriptor" => [
                    "passenger" => [
                        "first_name"=>null,//nombre del pasajero
                        "last_name"=>null //apellido del pasajero
                        ],
                    "route" => [
                        "departure"=>null,//ciudad de salida,
                        "destination"=>null,//ciudad de destino,
                        "departure_date_time"=>null,//Fecha y hora de salida. El formato válido es el siguiente - "yyyy-MM-ddTHH:mm:ss.sssZ". Ejemplo - 2023-12-31T09:37:52.000-04:00.,
                        "arrival_date_time"=>null,//Fecha y hora de llegada. El formato válido es el siguiente - "yyyy-MM-ddTHH:mm:ss.sssZ". Ejemplo - 2023-12-31T09:37:52.000-04:00.
                        "company"=>null
                        ]
                ]
            ]
        ],
        // El payer es quien realiza el pago. Este campo es un objeto que tiene la información del pagador.
        "payer" => [
            "first_name" => "Test",//Nombre del comprador
            "last_name" => "Test",//Es el campo de apellido del comprador.
            "phone" => [
                "area_code" => 57,//Código de área donde reside el comprador.
                "number" => "3184482848"//Número telefónico del comprador.
            ],
            "address" => [
                "zip_code"=>null,//codigo postal
                "street_name"=>null,//Calle donde vive el comprador.
                "street_number" => null//Número de la propiedad donde vive el comprador.
            ],
            "shipments" => [
                "receiver_address" => [//Objeto que comprende la dirección del destinatario de la compra.
                    "zip_code" => "12312-123",//Código postal
                    "state_name" => "Rio de Janeiro",//Provincia
                    "city_name" => "Buzios",//Ciudad
                    "street_name" => "Av das Nacoes Unidas",//Calle
                    "street_number" => 3003,//numero calle,
                    "floor"=>null,//piso de direccion
                    "apartment"=>null//Número de departamento de la dirección de entrega.
                ],
                "width" => null,//Ancho del código de barras
                "height" => null,//Altura del código de barras
                "express_shipment"=>null,//Indica si el envío es express o no. Los valores válidos son los siguientes - "1" si lo es, "0" si no lo es.
                "pick_up_on_seller"=>null//Indica si el cliente recogerá el producto en la dirección del vendedor. Los valores válidos son los siguientes - "1" si lo es, "0" si no lo es.
            ]
        ],
    ],
    "application_fee" => null,//Comisión (fee) que los terceros (integradores) cobran a sus clientes, en este caso vendedores, por utilizar la plataforma del marketplace y otros servicios. Este es un valor en reales que será definido por el integrador para el vendedor.
    "binary_mode" => false,//Cuando se configura como TRUE los pagos sólo pueden resultar aprobados o rechazados. Caso contrario también pueden resultar in_process.
    "callback_url"=>null,//URL a la cual Mercado Pago hace la redirección final (sólo para transferencia bancaria).
    "campaign_id" => null,//Es el identificador de la entidad que modela la fuente de los descuentos. Todos los cupones provienen de una sola campaña. La campaña configura, entre otras cosas, el saldo presupuestario disponible, fechas entre las cuales se pueden utilizar los cupones, reglas para la aplicación de los mismos, etc. Es la promesa de descuento.
    "capture" => false,//Es un campo booleano que se encuentra en pagos de dos pasos (como tarjeta de débito). En este tipo de pago, que se realiza de forma asíncrona, primero se reserva el valor de la compra (capture = false). Esta cantidad se captura y no se debita de la cuenta al instante. Cuando el dinero se transfiere realmente al cobrador (que recibe el pago), se captura la cantidad (capture = true).
    "coupon_amount" => null,//Es el valor del cupón de descuento. Por ejemplo - BRL 14,50. El tipo del atributo es BigDecimal.
    "description" => "Plan mensual  Cartmots",//Descripción del producto adquirido, el motivo del pago. Ej. - "Celular Xiaomi Redmi Note 11S 128gb 6gb Ram Original Global Blue Version" (descripción de un producto en el marketplace).
    "differential_pricing_id" => null,//Atributo que comúnmente contiene un acuerdo sobre cuánto se le cobrará al usuario (generalmente, este campo es más relevante para los pagos de Marketplace). Los precios y las tarifas se calculan en función de este identificador.
    "external_reference" => "MP0001",//Es una referencia de pago externa. Podría ser, por ejemplo, un hashcode del Banco Central, funcionando como identificador del origen de la transacción.
    "installments" => 1,//Cantidad seleccionada de cuotas
    "metadata" => null,//Este es un objeto clave-valor opcional en el que el cliente puede agregar información adicional que debe registrarse al finalizar la compra. Por ejemplo - {"payments_group_size":1,"payments_group_timestamp":"2022-11-18T15:01:44Z","payments_group_uuid":"96cfd2a4-0b06-4dea-b25f-c5accb02ba10"}
    "notification_url"=>null,//URL de Notificaciones disponibilizada para recibir las notificaciones de los eventos relacionados al Pago. La cantidad máxima de caracteres permitidos para enviar en este parámetro es de 248 caracteres.
    "payer" => [
        "entity_type" => "individual",//Tipo de entidad del pagador (sólo para transferencias bancarias) individual: Payer is individual.association: Payer is an association.
        "type" => "customer",//Tipo de identificación del pagador asociado (requerido si el pagador es un cliente) customer: Payer is a Customer and belongs to the collector. guest: The payer doesn't have an account.
        "email" => "baironmenesesidarraga.990128@gmail.com",//Correo electrónico asociado al payer. Este valor sólo devolverá una respuesta cuando status=approved, status=refunded o status=charged_back.
        "identification" => [
            "type" => "CC",//Se refiere al tipo de identificación. Puede ser de los siguientes tipos.
            // CPF: Individual Taxpayer Registration, Brazil.
            // CNPJ: National Register of Legal Entities, Brazil.
            // CUIT: Unique Tax Identification Code, Argentina.
            // CUIL: Unique Labor Identification Code, Argentina.
            // DNI: National Identity Document, Argentina.
            // CURP: Single Population Registration Code, Mexico.
            // RFC: Federal Registry of Taxpayers, Mexico.
            // CC: Citizenship Card, Colombia.
            // RUT: Single Tax List, Chile.
            // CI: Identity Card, Uruguay.
            "number" => "1143994831"//El número hace referencia al identificador del usuario en cuestión. Si es un CPF, por ejemplo, tendrá 11 números.
        ]
    ],
    "payment_method_id" => "master",//dentificador del medio de pago. Indica el ID del medio de pago seleccionado para realizar el pago. A continuación presentamos algunos ejemplos. Obtén todos los métodos de pago disponibles consultando la API de 'Obtener métodos de pago'.
    "statement_descriptor"=>"Pago plan mensual",
    "token" => "ff8080814c11e237014c1ff593b57b4d",//Identificador del token card. (obligatorio para tarjeta de crédito). El token de la tarjeta se crea a partir de la información de la propia tarjeta, aumentando la seguridad durante el flujo de pago. Además, una vez que el token se usa en una compra determinada, se descarta, lo que requiere la creación de un nuevo token para futuras compras.
    "transaction_amount" => 0,//Costo del producto. Ejemplo - La venta de un producto por R$ 100,00 tendrá un transactionAmount = 100.
];

$client->create($createRequest, $request_options);
});
