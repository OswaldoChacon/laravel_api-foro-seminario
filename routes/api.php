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
Route::post('forgot_password', 'UsuariosController@forgot_password');


// Route::apiResource('solicitud', 'SolicitudController');
Route::group(['middleware' => ['jwtAuth:Administrador'], 'namespace' => 'Administrador'], function () {

    Route::apiResource('usuarios', 'UsuarioController');
    Route::post('agregar_rolUsuario/{usuario}', 'UsuarioController@agregar_rolUsuario');
    Route::delete('eliminar_rolUsuario/{usuario}', 'UsuarioController@eliminar_rolUsuario');


    Route::apiResource('roles', 'RolController');    
    Route::apiResource('solicitudes', 'SolicitudController');

    Route::apiResource('lineas', 'LineaController');
    Route::apiResource('tiposProyecto', 'TipoProyectoController');

    Route::apiResource('foros', 'ForoController');
    Route::put('configurar_foro/{foro}', 'ForoController@configurar_foro');
    Route::put('activar_foro/{foro}', 'ForoController@activar_foro');
    Route::post('agregar_maestro/{foro}','ForoController@agregar_maestro');
    Route::get('proyectos/{foro}', 'ForoController@proyectos');


      

    Route::apiResource('fechaforo', 'FechaForoController');
    Route::post('agregar_break/{fecha}', 'FechaForoController@agregar_break');
    Route::delete('eliminar_break/{break}', 'FechaForoController@eliminar_break');


    Route::get('jurado', 'HorarioController@jurado');
    Route::put('proyecto/{proyecto}', 'ProyectoController@proyecto_participa');
    Route::post('asignar_jurado/{proyecto}', 'ProyectoController@asignar_jurado');
    Route::delete('eliminar_jurado/{proyecto}', 'ProyectoController@eliminar_jurado');

    Route::post('agregar_horarioJurado_all/{docente}', 'HorarioController@agregar_horarioJurado_all');
    Route::delete('eliminar_horarioJurado_all/{docente}', 'HorarioController@eliminar_horarioJurado_all');
    Route::post('agregar_horarioJurado/{docente}', 'HorarioController@agregar_horarioJurado');
    Route::delete('eliminar_horarioJurado/{docente}', 'HorarioController@eliminar_horarioJurado');
});



Route::group(['middleware' => ['jwtAuth:Administrador,Alumno'], 'namespace' => 'Administrador'], function () {
    Route::get('lineas', 'LineaController@index');
    Route::get('tiposProyecto', 'TipoProyectoController@index');
    Route::get('docentes', 'UsuarioController@docentes');
});

//Alumno
Route::group(['middleware' => ['jwtAuth:Alumno']], function () {
    Route::post('registrar_proyecto', 'AlumnoController@registrar_proyecto');

    

    Route::put('actualizar_proyecto/{proyecto}', 'AlumnoController@actualizar_proyecto');
    Route::put('enviar_solicitud/{proyecto}', 'AlumnoController@enviar_solicitud');
    Route::put('cancelar_solicitud/{proyecto}', 'AlumnoController@cancelar_solicitud');

    Route::get('registrar_solicitud', 'AlumnoController@get_registrar_solicitud');
    Route::post('registrar_solicitud', 'AlumnoController@registrar_solicitud');
});


Route::group(['middleware' => ['jwtAuth:Docente'], 'namespace' => 'Administrador'], function () {
    Route::put('permitir_cambios/{proyecto}','ProyectoController@permitir_cambios');
});




Route::group(['middleware' => ['jwtAuth:Alumno,Administrador,Docente']], function () {
  

    Route::get('foro_actual', 'AlumnoController@foro_actual');
    Route::get('lista_alumnos', 'AlumnoController@lista_alumnos');

    Route::get('misForos', 'UsuariosController@misForos');
    Route::get('misNotificaciones', 'UsuariosController@misNotificaciones');

    Route::get('miSolicitud', 'UsuariosController@miSolicitud');
    Route::put('responder_notificacion/{proyecto}', 'UsuariosController@responder_notificacion');
    Route::post('agregar_integrante', 'AlumnoController@agregar_integrante');
    Route::delete('eliminar_integrante', 'AlumnoController@eliminar_integrante');





    Route::get('mis_proyectos','UsuariosController@misProyectos');
});
