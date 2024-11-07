<?php

use App\Http\Controllers\SetpdfController;
use App\Http\Controllers\SolicitudCredito;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[SetpdfController::class,'pdfgenerate']);
Route::get('/solicitud',[SolicitudCredito::class,'view']);

