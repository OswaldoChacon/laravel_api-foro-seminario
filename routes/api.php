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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();

});

Route::post('/login','AuthController@login');

Route::get('/usuarios',function(){
    $users = App\User::paginate(7);
    $roles =  App\Roles::all();
    foreach($users as $user){
        // $user->roles = $roles;        
        foreach($roles as $rol){                                    
            $rol['is'] = $user->hasRole($rol->nombre);                                       
        }        
        $user->roles = $roles->toArray();        
    }
    $roles =  App\Roles::all();
    return response()->json($users,200);
});
// ->middleware('jwtAuth')

Route::post('registrar_usuario','OficinaController@registrar_usuario');
Route::put('agregar_rol/{usuario}','OficinaController@agregar_rol');
Route::delete('eliminar_rol/{usuario}','OficinaController@eliminar_rol');
Route::put('actualizar_usuario/{usuario}','OficinaController@actualizar_usuario');
Route::get('buscar_usuarios/{usuario}','OficinaController@buscar_Usuarios');
Route::get('usuario/{usuario}','OficinaController@getUsuario');
Route::delete('eliminar_usuario/{usuario}','OficinaController@eliminar_usuario');

Route::get('lineas','OficinaController@lineas');
Route::post('registrar_linea','OficinaController@registrar_linea');
Route::put('actualizar_linea/{linea}','OficinaController@actualizar_linea');
Route::get('buscar_lineas/{linea}','OficinaController@buscar_lineas');
Route::delete('eliminar_linea/{linea}','OficinaController@eliminar_linea');

Route::post('registrar_tipoProyecto','OficinaController@registrar_tipoProyecto');
Route::put('actualizar_tipoProyecto/{tipo}','OficinaController@actualizar_tipoProyecto');
Route::get('buscar_tiposProyecto/{tipo}','OficinaController@buscar_tiposProyecto');
Route::delete('eliminar_tipoProyecto/{tipo}','OficinaController@eliminar_tipoProyecto');

Route::get('foros','OficinaController@foros');
Route::post('registrar_foro','OficinaController@registrar_foro');
Route::put('actualizar_foro/{foro}','OficinaController@actualizar_foro');
Route::delete('eliminar_foro/{foro}','OficinaController@eliminar_foro');
Route::get('obtener_foro/{foro}','OficinaController@obtener_foro');
Route::put('configurar_foro/{foro}','OficinaController@configurar_foro');
Route::put('activar_foro/{foro}','OficinaController@activar_foro');
Route::put('desactivar_foro/{foro}','OficinaController@desactivar_foro');
Route::post('agregar_foroDocente/{foro}','OficinaController@agregar_foroDocente');

Route::post('agregar_fechaForo/{foro}','HorarioController@agregar_fechaForo');
Route::get('obtener_fechaForo/{fecha}','HorarioController@obtener_fechaForo');
Route::put('actualizar_fechaForo/{fecha}','HorarioController@actualizar_fechaForo');
Route::delete('eliminar_fechaForo/{fecha}','HorarioController@eliminar_fechaForo');

Route::post('agregar_break/{fecha}','HorarioController@agregar_break');
Route::delete('eliminar_break/{break}','HorarioController@eliminar_break');

Route::get('proyectos/{foro}','HorarioController@proyectos_foro');
Route::get('jurado','HorarioController@jurado');
Route::post('asignar_jurado/{proyecto}','OficinaController@asignar_jurado');
Route::delete('eliminar_jurado/{proyecto}','OficinaController@eliminar_jurado');
Route::put('proyecto/{proyecto}','HorarioController@proyecto_participa');
Route::post('agregar_horarioJurado/{docente}','HorarioController@agregar_horarioJurado');
Route::delete('eliminar_horarioJurado/{docente}','HorarioController@eliminar_horarioJurado');


Route::post('registrar_proyecto','AlumnoController@registrar_proyecto')->middleware('jwtAuth');
Route::put('actualizar_info/{usuario}','AlumnoController@actualizar_info');


// Route::('','AlumnoController@_proyecto');
// Route::('','AlumnoController@_proyecto');
// agregar_foroDocente




//Alumno

Route::get('foro_actual','AlumnoController@foro_actual')->middleware('jwtAuth');
