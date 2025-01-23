<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>solicitud</title>
</head>
<style  type="text/css">
    .titulo-cartmots{
       font-size: 12px; 
    }
    .pdf{
       margin: 0; 
       font-family: sans-serif;
       text-transform: capitalize;
    }
    
    .img-footer{
        width: 100%;
        height: 10rem;
    }
    .img-ico{
        width: 6rem;
        height: 6rem;
        background-image: cover;
    }
    .contenedor-imagen{
        width: 100%;
        width: auto;
        text-align: center;
        padding: 10px;
    }
    .img-vehiculo{
        width: auto;
        height: auto;
        max-width: 30rem;
        max-height: 30rem;
        margin: 0 auto;
        border-radius: 10px;
    }
    .text-center{
        text-align: center;
    }
    .font-bold{
        font: bold;
        font-size: 20px;
    }
    .contenedor-tabla{
        text-align: center;
        align-items: center;
        width: 100%;
    }
    .w-full{
        width: 100%
    }
    .w-33{
        width: 48.3%;
    }
    .w-10{
        width: 10rem;
    }
    .items-center{
        align-items: center;
    }
    .rounded-xl{
        border-radius: 20px;
    }
    .bg-slate-500{
        background: #B5B5B5;
    }
    .bg-slate-200{
        background: #EFE9E9;
    }
    .border-none{
        text-decoration: none;
        border-collapse: collapse;
    }
    .mt-2{
        margin-top: 2rem;
    }
    .text-sm{
        font-size: 13px
    }
    .h-10{
        height: 10rem;
    }
    .h-99{
        height: 99rem;
    }
    .h-2{
        height: 1rem;
    }
    .h-full{
        height: 100%;
    }
    .p-1{
        padding: 1rem;
    }
    .flex{
        display: flex;
        flex-direction: row;
        gap:1rem;
    }
    .flex-row{
        flex-direction: row;
    }
    .grid{
        display: grid;
    }
    .grid-template-col-3{
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .grid-template-crow-3{
        grid-template-row: repeat(3, minmax(0, 1fr));
    }
    .grid-template-crow-4{
        grid-template-row: repeat(4, minmax(0, 1fr));
    }
    .border-2{
        border:solid 1px gray;
        padding: 0.2rem;
        
    }
    .rounded-full{
        border-radius: 100%
    }
    .m-0{
        margin: 0;
    }
    .items-between{
        justify-content: space-between;
    }
    .text-left{
        text-align: left;
    }
    .border-4{
        border: 4px solid gray;
    }
    .p-0{
        padding: 0;
    }
    .text-lg{
        font-size: 8px;
        font-weight:bold;
        font-family: sans-serif;
    }
    .text-xl{
        font-size: 24px;
        font-weight:bold;
        font-family: sans-serif;
    }
    .titulo{
        background: gray;
        color:white;
       font-family: sans-serif;
        font-size: 15px;
        
    }
    .border-l{
        border-left: 0;
        border-bottom: 1px solid black;
        border-right: 1px solid black;
        border-top: 1px solid black;
    }
    .border-r{
        border-left: 1px solid black;
        border-bottom: 1px solid black;
        border-right: 0px solid black;
        border-top: 1px solid black;
    }
    .border-1-l{
        border-left:  1px solid gray;
    }
    .border-1-t{
        border-top:  1px solid gray;
    }
    .border-1-r{
        border-right:  1px solid gray;
    }
    .border-1-b{
        border-bottom:  1px solid gray;
    }
    .page-break {
    page-break-after: always;
    }
    .text-1-sm{
        font-size: 7px;
    }
    .text-sm{
        font-size: 12px;
    }
    .justify-lef{
        text-align: justify;
    }
    .mt-1{
        margin: 1rem;
    }
    .h-20{
        height: 5rem;
    }
    .w-5{
        width: 5rem;
    }
    .items-star{
        align-items: start;
    }
</style>
<body class="pdf">
            <div class="p-0 m-0">

                <h1 class="text-xl text-center">Solicitud para el estudio de credito persona natural</h1>
                <table class="w-full text-sm text-center">
                    <tr>
                        <td>Fecha De Expediccion: <p class="text-lg">{{$fecha}}</p></td>
                        <td>No.Solicitud:<p class="text-lg">0000001</p></td>
                       
                    </tr>
                </table>
                <h2 class="text-lg text-center">datos requeridos por los establecimientos de credito y entidades aseguradoras</h2>
                <table class="w-full text-sm text-center">
                    <tr>
                        <td>tipo:</td>
                        <td>Asesor:{{$usuario}}</td>
                        <td>c.c asesor:{{$cedulausuario}}</td>
                        <td>vitrina:</td>
                    </tr>
                </table>
        
               
              
            {{-- segundo cuadro --}}
            <h1 class="w-full text-center titulo p-0 m-0">Datos Del credito</h1>
            <div class="">
                <table class="w-full itemns-center text-lg text-center border-none">
                    <tbody class="p-0 m-0">
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left  border-1-t border-1-b  border-1-r border-1-l w-full h-2 text-lg">Marca: {{$marca}}</td>
                            <td class="text-left  border-1-t border-1-b  border-1-r w-full h-2 text-lg">Valor Vehiculo: {{$vehiculo}}</td>
                            <td class="text-left  border-1-t border-1-b  border-1-r w-full h-2 text-lg">Tipo: {{$tipo}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Vehiculo: {{$vehiculo}}</td>
                            <td class="text-left border-1-b border-1-r  w-full h-2 text-lg">Valor Cuota Extra: {{$valorcuotaextra}}</td>
                            <td class="text-left border-1-b border-1-r w-full h-2 text-lg">Cuota inicial: {{$cuotainicial}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Valor Seguro: {{$valorseguro}}</td>
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Valor a Financiar: {{$valorfinanciar}}</td>
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Plazo: {{$plazo}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0  ">
                            <td class="text-left border-1-b border-1-l  w-full h-2 text-lg">Plan: {{$plan}}</td>
                            <td class="text-left border-1-b  border-1-r w-33 h-2 text-lg"></td>
                            <td class="text-left border-1-b border-1-l border-1-r w-33 h-2 text-lg">Tasa: {{$tasa}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left w-full h-2 border-1-b  border-1-l text-lg">Financiera: {{$Financiera}}</td>
                            <td class="text-left w-full h-2 border-1-b  text-lg"></td>
                            <td class="text-left w-full h-2 border-1-b  border-1-r text-lg"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <h1 class="w-full text-center titulo p-0 m-0">Datos Personales del cliente</h1>
            <div class="p-0 m-0">
                <table class="w-full itemns-center text-lg text-center border-none p-0 m-0">
                    <tbody class="p-0 m-0">
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left  border-1-t border-1-b border-1-l w-full h-2 text-lg">Nombre Completo: {{$nombre}} {{$apellido}}</td>
                            <td class="text-left  border-1-t border-1-b w-full h-2 text-lg"></td>
                            <td class="text-left  border-1-t border-1-b w-full h-2 text-lg"></td>
                            <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg"></td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Fecha Nacimiento: {{$fechanacimiento}}</td>
                            <td class="text-left border-1-b border-1-r  w-full h-2 text-lg">Tipo de Documento: {{$tipodedocumento}}</td>
                            <td class="text-left border-1-b w-full h-2 text-lg">Numero de Identificacion: {{$numerodoc}}</td>
                            <td class="text-left border-1-b border-1-r   w-full h-2 text-lg"></td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Ciudad: {{$ciudad}}</td>
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Genero: {{$genero}}</td>
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Estado Civil: {{$estadocivil}}</td>
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Fecha de expediccion: {{$fechaespediccion}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0  ">
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Direccion: {{$direccion}}</td>
                            <td class="text-left border-1-b border-1-l border-1-r w-33 h-2 text-lg">Telefono: {{$telefono}}</td>
                            <td class="text-left border-1-b border-1-l  w-33 h-2 text-lg">Celular: {{$celular}}</td>
                            <td class="text-left border-1-b border-1-r w-33 h-2 text-lg"></td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left w-full h-2 border-1-b border-1-r border-1-l text-lg">Tipo Vivienda: {{$tipodevivienda}}</td>
                            <td class="text-left w-full h-2 border-1-b border-1-r border-1-l text-lg">Email: {{$email}}</td>
                            <td class="text-left w-full h-2 border-1-b  border-1-l text-lg">Antiguedad Vivienda: {{$antiguedadvivienda}}</td>
                            <td class="text-left w-full h-2 border-1-b border-1-r  text-lg"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {{-- segundo cuadro --}}
            <h1 class="w-full text-center titulo p-0 m-0">Informacion Laboral</h1>
            <div class="p-0 m-0">
                <table class="w-full itemns-center text-lg text-center border-none">
                    <tbody class="p-0 m-0">
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Tipo de trabajo: {{$contrato}}</td>
                            <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Profesion: {{$profesion}}</td>
                            <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Nit: {{$nit}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Empresa: {{$empresa}}</td>
                            <td class="text-left border-1-b border-1-r  w-full h-2 text-lg">Antiguedad laboral: {{$antiguedad}}</td>
                            <td class="text-left border-1-b border-1-r w-full h-2 text-lg">Telefono: {{$telefono}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Detalle:</td>
                            <td class="text-left border-1-b  w-full h-2 text-lg">Direccion: {{$direccion}}</td>
                            <td class="text-left border-1-b  border-1-r w-full h-2 text-lg"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {{-- segundo cuadro --}}
            <div class="p-0 m-0">
                <h1 class="w-full text-center titulo p-0 m-0">Ingresos y Egresos</h1>
                <div class="p-0 m-0">
                    <table class="w-full itemns-center text-lg text-center border-none">
                        <tbody class="p-0 m-0">
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Ingreso Fijo: {{$ingresofijo}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Ingreso Variable: {{$ingresovariable}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Otros Ingresos: {{$otrosingresos}}</td>
                            </tr>
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Gastos: {{$gastos}}</td>
                                <td class="text-left border-1-b border-1-r  w-full h-2 text-lg">Otros Egresos: {{$otrosegresos}}</td>
                                <td class="text-left border-1-b border-1-r w-full h-2 text-lg">Tiene Vehiculo: {{$tienevehiculo}}</td>
                            </tr>
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Total Ingresos: {{$totalingreso}}</td>
                                <td class="text-left border-1-b  border-1-r w-full h-2 text-lg">Total activos: {{$totalactivos}}</td>
                                <td class="text-left border-1-b border-1-l border-1-r w-full h-2 text-lg">Total Pasivo: {{$totalpasivos}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            {{-- segundo cuadro --}}
            <h1 class="w-full text-center titulo p-0 m-0">Referencias Personales</h1>
            <div class="">
                <table class="w-full itemns-center text-lg text-center border-none">
                    <tbody class="p-0 m-0">
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Nombre: {{$ref1nombre}}</td>
                            <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Telefono: {{$ref1telefono}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Ciudad: {{$ref1ciudad}}</td>
                            <td class="text-left border-1-b border-1-r  w-full h-2 text-lg">Direccion: {{$ref1direccion}}</td>
                        </tr>
                        <tr>
                            <td class="w-full"></td>
                            <td class="w-full"></td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Nombre: {{$ref2nombre}}</td>
                            <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Telefono: {{$ref2telefono}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Ciudad: {{$ref2ciudad}}</td>
                            <td class="text-left border-1-b border-1-r  w-full h-2 text-lg">Direccion: {{$ref2direccion}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {{-- segundo cuadro --}}
            <h1 class="w-full text-center titulo p-0 m-0">Referencias Familiares</h1>
            <div class="">
                <table class="w-full itemns-center text-lg text-center border-none">
                    <tbody class="p-0 m-0">
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Nombre: {{$familiar1nombre}}</td>
                            <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Telefono: {{$familiar1telefono}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Ciudad: {{$familiar1ciudad}}</td>
                            <td class="text-left border-1-b border-1-r  w-full h-2 text-lg">Direccion: {{$familiar1direccion}}</td>
                        </tr>
                        <tr>
                            <td class="w-full"></td>
                            <td class="w-full"></td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Nombre: {{$familiar2nombre}}</td>
                            <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Telefono: {{$familiar2telefono}}</td>
                        </tr>
                        <tr class="contenedor-tabla w-full  items-between m-0 ">
                            <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Ciudad: {{$familiar2ciudad}}</td>
                            <td class="text-left border-1-b border-1-r  w-full h-2 text-lg">Direccion: {{$familiar2direccion}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div>

                <h1 class="w-full text-center titulo p-0 m-0">Referencias Empresariales-Comerciales</h1>
                <div class="">
                    <table class="w-full itemns-center text-lg text-center border-none">
                        <tbody class="p-0 m-0">
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Nombre: {{$empresarial1nombre}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Nit: {{$empresarial1nit}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Telefono: {{$empresarial1telefono}}</td>
                            </tr>
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Ciudad: {{$empresarial1ciudad}}</td>
                                <td class="text-left border-1-b   w-full h-2 text-lg">Direccion: {{$empresarial1direccion}}</td>
                                <td class="text-left border-1-b border-1-r  w-full h-2 text-lg"></td>
                            </tr>
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left w-full  text-lg"></td>
                                <td class="text-left   w-full  text-lg"></td>
                                <td class="text-left  w-full  text-lg"></td>
                            </tr>
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Nombre: {{$empresarial2nombre}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Nit: {{$empresarial2nit}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Telefono: {{$empresarial2telefono}}</td>
                            </tr>
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Ciudad: {{$empresarial2ciudad}}</td>
                                <td class="text-left border-1-b   w-full h-2 text-lg">Direccion: {{$empresarial2direccion}}</td>
                                <td class="text-left border-1-b border-1-r  w-full h-2 text-lg"></td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
                <h1 class="w-full text-center titulo p-0 m-0">Informacion del Conyugue</h1>
                <div class="">
                    <table class="w-full itemns-center text-lg text-center border-none">
                        <tbody class="p-0 m-0">
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Conyugue: {{$conyugenombre}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Fecha Nacimiento: {{$conyugefecha}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg">Cedula: {{$conyugecedula}}</td>
                            </tr>
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Trabaja: {{$conyugeemporesa}}</td>
                                <td class="text-left border-1-b   w-full h-2 text-lg">Telefono: {{$conyugetelefono}}</td>
                                <td class="text-left border-1-b border-1-r border-1-l w-full h-2 text-lg">Salario: {{$conyugesalario}}</td>
                            </tr>
                    
                            <tr class="contenedor-tabla w-full  items-between m-0 ">
                                <td class="text-left  border-1-t border-1-b border-1-r border-1-l w-full h-2 text-lg">Otros Ingresos: {{$conyugeotrosingresos}}</td>
                                <td class="text-left  border-1-t border-1-b  w-full h-2 text-lg">Egresos: {{$conyugeegresos}}</td>
                                <td class="text-left  border-1-t border-1-b border-1-r w-full h-2 text-lg"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
           
           
        </div>
        <p class="text-1-sm text-center">Desarrollado Por CartMots/SYPRODS</p>
         <div class="page-break"></div>
         <div class="w-full">
            <h1 class="text-lg text-center">Autorizacion para consulta y reporte a centrales de riesgo, beneficios y proteccion</h1>
            <div class="border-2 text-sm p-1 justify-lef">
                <p>YO ___________ con Cédula de ciudadanía número _________________ actuando en nombre propio y de manera
                    autónoma. Autorizo expresa y ampliamente EMPRESA {{$nombre_empresa}} ,a las Entidades Financieras Aliadas o cualquiera que represente
                    sus derechos, para que compartan con corporaciones, entidades avaladoras, y/o empresas con las que estas establezcan alianzas, la
                    información que he diligenciado en este documento, consulten, procesen, soliciten, reporten y divulguen ante las entidades de consulta
                    y Operadores de Información y Riesgo y demás entidades financieras de Colombia, o a cualquier entidad que maneje o administre bases
                    de datos con los mismos fines, mi información personal, comercial y financiera, así como la información referente a mi comportamiento
                    comercial y/o crediticio, y así evalúen la posibilidad de otorgarme los productos y servicios de dichas entidades y me contacten para
                    ofrecerme sus productos y servicios .. Esta información podrá ser compartida con terceros nacionales o extranjeros que realizan este tipo
                    de labores, en calidad de aliados o proveedores, los cuales mantendrán la confidencialidad de la misma y no podrán utilizarla para un fin
                    diferente al de desarrollar las actividades para las cuales se les ha entregado. Manifiesto que conozco los derechos que tengo como
                    titular de la información, tales como conocer, actualizar y rectificar mis datos personales, entre otros, de conformidad con la Ley 1581 de
                    2012 y sus derechos.
                    Lo anterior también implica que el incumplimiento de mis obligaciones se reflejará en las mencionadas bases de datos durante el tiempo
                    que establezcan las normas que regulan la materia de acuerdo con los términos y condiciones definidos por ellos. Entiendo que EMPRESA__
                    {{$nombre_empresa}}. no asume responsabilidad alguna por la aprobación o negación del crédito por parte de las entidades financieras,
                    entidades avaladoras y/o empresas con las que estas establezcan alianzas comerciales u otras empresas, ni se compromete a obtener
                    la aprobación de este por cuanto simplemente actúa como un canal de información entre el solicitante del crédito y la(s) señalada(s)
                    empresa(s). Autorizo voluntariamente a la entidad financiera, corporación, entidad avaladora, empresa para enviar mensajes relativos a
                    mis obligaciones crediticias y/o relacionadas con
                    dicha entidad, al terminal móvil de telecomunicaciones y/o a la dirección electrónica reportados como de mi uso o propiedad. Así mismo
                    autorizo voluntaria e irrevocablemente a dicha entidad para que me fotografíe, conserve las mismas, las utilice para propósitos
                    relacionados con mi identificación como cliente de la entidad y las haga valer ante las autoridades judiciales en caso de ser requerido.
                    En caso de ser aprobada esta solicitud de crédito, autorizo a la Entidad Financiera Aliada que me contacte a través del envío de mensajes
                    por correo electrónico y/o cualquier medio, para notificarme de dicha aprobación
                    Para conocer la Política para la administración de datos personales, ingrese al sitio web de las Entidades Financieras Aliadas.
                    El origen de fondos y/o bienes de mi propiedad proviene de Salario _ ; Honorarios _ ; Arrendamientos _ ; Comisiones _ ;H ere
                    n c i a_; Re n d i m i e n tos fi n a n c i e ros_; Ve n ta d e p ro p i e d ad_; Pe n s i ó n I a b o r a I __ ;
                    Otros Cuál? ------------
                    Los recursos que devengo, así como los que entregué, no provienen de ninguna de las actividades ilícitas contempladas por la ley.
                    Declara renta: Si __ No _ De conformidad con lo dispuesto en el artículo 32 del decreto 2798 de 1994 emanado por el Gobierno
                    Nacional, manifiesto que, por el año gravable inmediatamente anterior, no me encuentro obligado a presentar declaración de renta. Así
                    mismo declaro que la información contenida en esta solicitud es verídica (art 83 de la Constitución Política de Colombia), así mismo
                    autorizo a la entidad financiera, corporación, entidad avaladora, y/o empresa con la que esta establezca alianzas que haya aprobado el
                    cupo de crédito para reportar, solicitar y divulgar mi información crediticia ante cualquier central de información autorizada según las
                    condiciones establecidas por las entidades de control y vigilancia respectiva. Declaro haber leído cuidadosamente el contenido de este
                    documento y haberlo comprendido a cabalidad por lo cual entiendo sus alcances e implicaciones.
                    Con quienes haya suscrito convenios de compra/venta de cartera o a quien represente sus derechos u ostente en el futuro a cualquier
                    titulo la calidad de acreedor, para que por el medio, que considere pertinente y seguro, consulte mis datos personales relacionados con la
                    afiliación y pago de los aportes al sistema de seguridad social integral, tales como ingresos base a cotización y demás informaciones,
                    relacionadas con mi situación laboral y empleador, así como para solicitar a los operadores de información del PILA, y a estos a su vez,
                    para que le sea suministrada, igualmente autorizo (autorizamos) a consultar, reportar, procesar, solicitar, divulgar, modificar, actualizar a
                    la central de información del sectorfinanciero-CIFIN, a DATACREDITO o a cualquiera otra entidad que administre base de datos con los
                    mismos fines, toda la información referente a mi (nuestro) comportamiento como cliente(s). Lo anterior implica que esta autorización
                    incluye que el cumplimiento o incumplimiento de mi (nuestro) obligaciones se refleja en los reportes que sobre mi (nuestro) habito de
                    pago realizan las mencionadas centrales de información financiera, en donde se consignan de manera completa todos los datos
                    referentes a mi (nuestro) actual y pasado comportamiento frente a las obligaciones del sector financiero, y en general frente al
                    cumplimiento de mi (nuestras) obligaciones". Autorizo Consultar y/o verificar la información de mi titularidad depositada o existente en
                    cualquier organismo o entidad de cualquier naturaleza, incluyendo organismos y entidades que recopilan datos de la seguridad social y
                    tratar dicha información con base en lo aquí autorizado, en particular para validación de información y demás finalidades descritas.
                </p>
            </div>
            <div class="border-2 justify-lef">
                <p class="text-sm ">Declaró que la información contenida en esta solicitud es para reportar solicitar y divulgar mi información crediticia ante cualquier
                    central de información autorizada según las condiciones establecidas por las entidades de control y vigilancia respectiva.</p>
            </div>
          
           
            
         </div>
         <div class="w-full h-2">

         </div>
         <table>
            <tr>
                <td>
                    <div class="w-5 h-20 border-2">
                
                    </div>
                </td>
                <td class="items-star">
                    <p class="text-lg">Nombre:</p>
                    <p class="text-lg">C.C:</p>
                    <p class="text-lg">Firma:_______________________________________________</p>
                </td>
            </tr>
         </table>
         
         <p class="text-lg">Huella indice Derecho</p>
</body>
</html>