<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tymon\JWTAuth\Facades\JWTAuth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('login', 'AuthController@login');
Route::post('forgot_password', 'AuthController@restablecerPassword');


Route::group(['middleware' => ['jwtAuth:Taller,Administrador']], function () {
    Route::apiResource('usuarios', 'UsuarioController');
});



// organizar los permisos de este middleware
Route::group(['middleware' => ['jwtAuth:Alumno,Administrador,Docente']], function () {

    Route::get('datos_personales', 'AuthController@datosPersonales');
    Route::put('actualizar_datos/{usuario}', 'AuthController@actualizarDatos');
    Route::put('cambiar_contrasena', 'AuthController@cambiarContrasena');

    Route::get('mis_foros', 'UsuariosController@misForos');
    Route::get('mis_notificaciones', 'UsuariosController@misNotificaciones');


    Route::put('responder_notificacion/{proyecto}', 'UsuariosController@responderNotificacion');
    Route::post('agregar_integrante', 'AlumnoController@agregarIntegrante');
    Route::delete('eliminar_integrante', 'AlumnoController@eliminarIntegrante');
    Route::get('mis_proyectos', 'UsuariosController@misProyectos');

    Route::get('plantillas/grupos/{id}', 'GrupoController@grupos');
    Route::get('plantillas/grupos/conceptos/{id}', 'ConceptoController@conceptos');
    Route::apiResource('plantillas','PlantillaController')->except('show');
    Route::apiResource('plantillas/{plantilla}/grupos','GrupoController');
    Route::apiResource('plantillas/grupos/conceptos','ConceptoController');
});
