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

Route::post('/login', 'AuthController@login');
Route::post('forgot_password', 'UsuariosController@forgot_password');

// Route::group(['middleware' => ['jwtAuth:Administrador']], function () {
    Route::get('roles', 'OficinaController@roles');
    Route::post('agregar_rol', 'OficinaController@agregar_rol');
    Route::get('actualizar_rol/{rol}', 'OficinaController@actualizar_rol');
    Route::delete('eliminar_rol/{rol}', 'OficinaController@eliminar_rol');

    Route::get('usuarios', 'OficinaController@usuarios');
    Route::post('registrar_usuario', 'OficinaController@registrar_usuario');
    Route::put('actualizar_usuario/{usuario}', 'OficinaController@actualizar_usuario');
    Route::delete('eliminar_usuario/{usuario}', 'OficinaController@eliminar_usuario');
    Route::put('agregar_rolUsuario/{usuario}', 'OficinaController@agregar_rolUsuario');
    Route::delete('eliminar_rolUsuario/{usuario}', 'OficinaController@eliminar_rolUsuario');

    Route::get('lineas', 'OficinaController@lineas');
    Route::post('registrar_lineas', 'OficinaController@registrar_linea');
    Route::put('actualizar_lineas/{linea}', 'OficinaController@actualizar_linea');
    Route::delete('eliminar_lineas/{linea}', 'OficinaController@eliminar_linea');

    Route::get('tiposProyecto', 'OficinaController@tiposProyecto');
    Route::post('registrar_tiposProyecto', 'OficinaController@registrar_tipoProyecto');
    Route::put('actualizar_tiposProyecto/{tipo}', 'OficinaController@actualizar_tipoProyecto');
    Route::delete('eliminar_tiposProyecto/{tipo}', 'OficinaController@eliminar_tipoProyecto');

    Route::get('foros', 'OficinaController@foros');
    Route::get('obtener_foro/{foro}', 'OficinaController@obtener_foro');
    Route::post('registrar_foro', 'OficinaController@registrar_foro');
    Route::put('actualizar_foro/{foro}', 'OficinaController@actualizar_foro');
    Route::delete('eliminar_foro/{foro}', 'OficinaController@eliminar_foro');

    Route::put('configurar_foro/{foro}', 'OficinaController@configurar_foro');
    Route::put('activar_foro/{foro}', 'OficinaController@activar_foro');
    Route::post('agregar_foroDocente/{foro}', 'OficinaController@agregar_foroDocente');
// });






Route::post('agregar_fechaForo/{foro}', 'HorarioController@agregar_fechaForo');
Route::get('obtener_fechaForo/{fecha}', 'HorarioController@obtener_fechaForo');
Route::put('actualizar_fechaForo/{fecha}', 'HorarioController@actualizar_fechaForo');
Route::delete('eliminar_fechaForo/{fecha}', 'HorarioController@eliminar_fechaForo');

Route::post('agregar_break/{fecha}', 'HorarioController@agregar_break');
Route::delete('eliminar_break/{break}', 'HorarioController@eliminar_break');

Route::get('proyectos/{foro}', 'HorarioController@proyectos_foro');
Route::get('jurado', 'HorarioController@jurado');
Route::post('asignar_jurado/{proyecto}', 'OficinaController@asignar_jurado');
Route::delete('eliminar_jurado/{proyecto}', 'OficinaController@eliminar_jurado');
Route::put('proyecto/{proyecto}', 'HorarioController@proyecto_participa');
Route::post('agregar_horarioJurado/{docente}', 'HorarioController@agregar_horarioJurado');
Route::delete('eliminar_horarioJurado/{docente}', 'HorarioController@eliminar_horarioJurado');


Route::post('registrar_proyecto', 'AlumnoController@registrar_proyecto')->middleware('jwtAuth');
Route::put('actualizar_info/{usuario}', 'AlumnoController@actualizar_info');
Route::put('cambiar_contrasena', 'UsuariosController@cambiar_contrasena')->middleware('jwtAuth:Alumno,Docente,Administrador');

// Route::('','AlumnoController@_proyecto');
// Route::('','AlumnoController@_proyecto');
// agregar_foroDocente




//Alumno

Route::get('foro_actual', 'AlumnoController@foro_actual')->middleware('jwtAuth');
