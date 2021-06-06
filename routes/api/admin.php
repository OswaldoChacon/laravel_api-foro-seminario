<?php

use App\Http\Controllers\TipoDeSolicitudController;
use App\TipoDeSolicitud;
use Illuminate\Support\Facades\Route;




Route::group(['middleware' => ['jwtAuth:Administrador']], function () {
    Route::post('agregar_rol', 'UsuarioController@agregarRol');
    Route::delete('eliminar_rol/{usuario}', 'UsuarioController@eliminarRol');

    Route::apiResource('roles', 'RolController')->parameters(['roles' => 'rol']);
    // Route::apiResource('tipos-de-solicitudes', TipoDeSolicitudController::class)->parameters(['tipos-de-solicitudes' => 'tipoDeSolicitud']);
    // Route::apiResource('lineas-de-investigacion', LineaDeInvestigacionController::class)->parameters(['lineas-de-investigacion'=>'lineaDeInvestigacion']);
    // Route::apiResource('tipos-de-proyectos', TipoDeProyectoController::class)->parameters(['tipos-de-proyectos' => 'tipoDeProyecto']);
    // Route::apiResource('foros', ForoController::class);
    Route::apiResource('tipos-de-solicitudes', 'TipoDeSolicitudController')->parameters(['tipos-de-solicitudes' => 'tipoDeSolicitud']);
    Route::apiResource('lineas-de-investigacion', 'LineaDeInvestigacionController')->parameters(['lineas-de-investigacion'=>'lineaDeInvestigacion']);
    Route::apiResource('tipos-de-proyectos', 'TipoDeProyectoController')->parameters(['tipos-de-proyectos' => 'tipoDeProyecto']);
    Route::apiResource('foros', 'ForoController');
    Route::apiResource('foros.fechas-foro', 'FechaForoController')->parameters(['fecha-foro'=>'fechaForo']);
    Route::apiResource('foros.fechas-foro.recesos', 'FechaForoController');

    Route::put('configurar_foro/{foro}', 'ConfigurarForoController@configurarForo');
    Route::put('activar_foro/{foro}', 'ConfigurarForoController@activarForo');
    Route::post('agregar_maestro/{foro}', 'ConfigurarForoController@agregarMaestro');
    Route::get('proyectos/{foro}', 'ConfigurarForoController@proyectos');

    Route::post('agregar_break/{fecha}', 'FechaForoController@agregarBreak');
    Route::delete('eliminar_break/{break}', 'FechaForoController@eliminarBreak');

    Route::put('proyecto_participa/{proyecto}', 'ProyectoController@proyectoParticipa');

    Route::get('jurado', 'JuradoController@jurado');
    Route::post('asignar_jurado/{proyecto}', 'JuradoController@asignarJurado');
    Route::delete('eliminar_jurado/{proyecto}', 'JuradoController@eliminarJurado');

    Route::post('agregar_horario_all', 'HorarioController@agregarHorarioAll');
    Route::delete('eliminar_horario_all/{docente}', 'HorarioController@eliminarHorarioAll');
    Route::post('agregar_horario', 'HorarioController@agregarHorario');
    Route::delete('eliminar_horario/{docente}', 'HorarioController@eliminarHorario');


    Route::post('generar_horario', 'GenerarHorarioController@generarHorario');
    Route::get('proyectos_maestros', 'GenerarHorarioController@proyectosMaestros');
});
