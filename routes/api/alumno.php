<?php

use Illuminate\Support\Facades\Route;


// Route::group(['middleware' => ['jwtAuth:Administrador,Alumno']], function () {
Route::group(['middleware' => ['jwtAuth:Alumno']], function () {

    Route::group(['prefix' => 'alumno'], function () {
        Route::get('lineas', 'LineaDeInvestigacionController@index');
        Route::get('tiposProyecto', 'TipoProyectoController@index');
        Route::get('docentes', 'UsuarioController@docentes');
    });

    // Route::post('registrar_proyecto', 'ProyectoController@registrarProyecto');
    // Route::put('actualizar_proyecto/{proyecto}', 'ProyectoController@actualizarProyecto');
    




    Route::get('proyecto_actual', 'ProyectoController@proyectoActual');
    
    Route::put('enviar_solicitud/{proyecto}', 'AlumnoController@enviarSolicitud');
    Route::put('cancelar_solicitud/{proyecto}', 'AlumnoController@cancelarSolicitud');
    Route::get('registrar_solicitud', 'AlumnoController@getRegistrarSolicitud');
    Route::post('registrar_solicitud', 'AlumnoController@registrarSolicitud');

    Route::get('lista_alumnos', 'UsuarioController@listaAlumnos');
    Route::get('foro_actual', 'ForoController@foroActual');
    
    
});
