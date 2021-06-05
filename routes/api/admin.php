<?php

use Illuminate\Support\Facades\Route;




// Route::group(['middleware' => ['jwtAuth:Administrador']], function () {
    Route::post('agregar_rol', 'UsuarioController@agregarRol');
    Route::delete('eliminar_rol/{usuario}', 'UsuarioController@eliminarRol');

    Route::apiResource('roles', 'RolController')->parameters(['roles' => 'rol']);
    Route::apiResource('solicitudes', 'TipoDeSolicitudController')->parameters(['solicitudes' => 'solicitud']);
    Route::apiResource('lineas', 'LineaDeInvestigacionController');
    Route::apiResource('tiposProyecto', 'TipoDeProyectoController')->parameters(['tiposProyecto'=>'tipoProyecto']);

    Route::apiResource('foros', 'ForoController');
    Route::put('configurar_foro/{foro}', 'ConfigurarForoController@configurarForo');
    Route::put('activar_foro/{foro}', 'ConfigurarForoController@activarForo');
    Route::post('agregar_maestro/{foro}', 'ConfigurarForoController@agregarMaestro');
    Route::get('proyectos/{foro}', 'ConfigurarForoController@proyectos');

    Route::apiResource('fechaforo', 'FechaForoController');
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
// });
