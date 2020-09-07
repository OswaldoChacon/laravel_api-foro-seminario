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


Route::group(['middleware' => ['jwtAuth:Administrador'], 'namespace' => 'Administrador'], function () {

    Route::apiResource('usuarios', 'UsuariosController');
    Route::put('agregar_rolUsuario/{usuario}', 'UsuariosController@agregar_rolUsuario');
    Route::delete('eliminar_rolUsuario/{usuario}', 'UsuariosController@eliminar_rolUsuario');    


    Route::apiResource('roles', 'RolesController');
    Route::apiResource('solicitudes', 'SolicitudesController');

    Route::apiResource('lineas', 'LineasController');
    Route::apiResource('tiposProyecto', 'TiposProyectoController');

    Route::apiResource('foros', 'ForosController');
    Route::put('configurar_foro/{foro}', 'ForosController@configurar_foro');
    Route::put('activar_foro/{foro}', 'ForosController@activar_foro');
    Route::get('proyectos/{foro}', 'ForosController@proyectos');

    
    Route::put('proyecto/{proyecto}', 'ProyectosController@proyecto_participa');
    //     Route::post('agregar_foroDocente/{foro}', 'OficinaController@agregar_foroDocente');

    //     Route::post('agregar_fechaForo/{foro}', 'HorarioController@agregar_fechaForo');
    //     Route::get('obtener_fechaForo/{fecha}', 'HorarioController@obtener_fechaForo');
    //     Route::put('actualizar_fechaForo/{fecha}', 'HorarioController@actualizar_fechaForo');
    //     Route::delete('eliminar_fechaForo/{fecha}', 'HorarioController@eliminar_fechaForo');

    Route::apiResource('fechaforo', 'FechaForoController');
    Route::post('agregar_break/{fecha}', 'FechaForoController@agregar_break');
    Route::delete('eliminar_break/{break}', 'FechaForoController@eliminar_break');


    Route::get('jurado', 'HorarioController@jurado');
    Route::post('asignar_jurado/{proyecto}', 'ProyectosController@asignar_jurado');
    Route::delete('eliminar_jurado/{proyecto}', 'ProyectosController@eliminar_jurado');
});





// Route::group(['middleware' => ['jwtAuth:Administrador']], function () {
//     Route::get('usuarios', 'OficinaController@usuarios');
//     Route::post('registrar_usuario', 'OficinaController@registrar_usuario');
//     Route::put('actualizar_usuario/{usuario}', 'OficinaController@actualizar_usuario');
//     Route::delete('eliminar_usuario/{usuario}', 'OficinaController@eliminar_usuario');
//     Route::put('agregar_rolUsuario/{usuario}', 'OficinaController@agregar_rolUsuario');
//     Route::delete('eliminar_rolUsuario/{usuario}', 'OficinaController@eliminar_rolUsuario');


// });



// Route::post('agregar_horarioJurado_all/{docente}', 'HorarioController@agregar_horarioJurado_all');
// Route::delete('eliminar_horarioJurado_all/{docente}', 'HorarioController@eliminar_horarioJurado_all');
// Route::post('agregar_horarioJurado/{docente}', 'HorarioController@agregar_horarioJurado');
// Route::delete('eliminar_horarioJurado/{docente}', 'HorarioController@eliminar_horarioJurado');





// ->middleware('jwtAuth:Alumno');

// Route::put('actualizar_info/{usuario}', 'AlumnoController@actualizar_info');
// Route::put('cambiar_contrasena', 'UsuariosController@cambiar_contrasena')->middleware('jwtAuth:Alumno,Docente,Administrador');





Route::group(['middleware' => ['jwtAuth:Administrador,Alumno'], 'namespace' => 'Administrador'], function () {
    Route::get('lineas', 'LineasController@index');
    Route::get('tiposProyecto','TiposProyectoController@index');
    Route::get('docentes','UsuariosController@docentes');
});

//Alumno
Route::group(['middleware'=>['jwtAuth:Alumno']],function(){
    Route::post('registrar_proyecto', 'AlumnoController@registrar_proyecto');

    // 
    // Route::get('lineas','Administrador\LineasController@index');
    // Route::get('tiposProyecto','Administrador\TiposProyectoController@index');
    // Route::get('roles','AlumnoController@roles');
    // Route::get('docentes','UsuariosController@docentes');

    Route::put('actualizar_proyecto/{proyecto}','AlumnoController@actualizar_proyecto');
    Route::put('enviar_solicitud/{proyecto}','AlumnoController@enviar_solicitud');

    Route::get('registrar_solicitud', 'AlumnoController@get_registrar_solicitud');
    Route::post('registrar_solicitud','AlumnoController@registrar_solicitud');
});





// ->middleware('jwtAuth:Alumno');


Route::group(['middleware' => ['jwtAuth:Alumno,Administrador,Docente']], function () {


    // Route::get('solicitudes', 'OficinaController@solicitudes');

    Route::get('foro_actual', 'AlumnoController@foro_actual');
    Route::get('lista_alumnos', 'AlumnoController@lista_alumnos');

    Route::get('misForos','UsuariosController@misForos');
    Route::get('misNotificaciones', 'UsuariosController@misNotificaciones'); 
    
    Route::get('miSolicitud', 'UsuariosController@miSolicitud');
    Route::put('responder_notificacion/{proyecto}', 'UsuariosController@responder_notificacion');
    Route::post('agregar_integrante/{proyecto}', 'AlumnoController@agregar_integrante');
    Route::delete('eliminar_integrante/{proyecto}', 'AlumnoController@eliminar_integrante');
});