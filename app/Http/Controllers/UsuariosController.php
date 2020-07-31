<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use App\Http\Requests\CambiarPasswordRequest;
use App\Notificaciones;
use App\User;
use App\Proyectos;

class UsuariosController extends Controller
{
    //
    public function cambiar_contrasena(CambiarPasswordRequest $request)
    {                  
        $usuarioLogueado = JWTAuth::user();
        $usuarioLogueado->password = bcrypt($request->nuevo_password);
        $usuarioLogueado->save();        
        return response()->json(['message'=>'Contraseña actualizada'], 200);
    }
    public function forgot_password(Request $request)
    {
        $request->validate([
            'email'=>'exists:users,email'
        ]);
        // Mail::to($request->email)->send(new ForgotPassword);

    }    
    public function misNotificaciones(){
        $usuario = User::where('num_control',15280701)->first();
        $misNotificaciones = $usuario->misNotificaciones()->get();
        return response()->json($misNotificaciones, 200);
        // dd($usuario->misNotificaciones()->get());
        
    }
    public function miSolicitud()
    {
        $usuario = User::where('num_control','prueba')->first();
        $miSolicitud = $usuario->miSolicitud()->with('receptor')->orderBy('respuesta','desc')->get();
        // dd($miSolicitud);
        return response()->json($miSolicitud, 200);
    }
    public function responder_notificacion(Request $request, $folio)
    {
        $usuario = User::where('num_control','15280701')->firstOrFail();
        $proyecto = Proyectos::where('folio',$folio)->firstOrFail();
        $notificacion = $usuario->misNotificaciones()->where([
            ['proyecto_id',$proyecto->id],
            ['receptor',$usuario->id]
        ])->firstOrFail();
        $notificacion->respuesta = $request->respuesta;
        $notificacion->save();
        $message = $request->respuesta ? 'Proyecto aceptado':'Proyecto rechazado';
        return response()->json(['message'=>$message], 200);
    }
    public function notificaciones($folio)
    {
        $proyecto = Proyectos::where('folio',$folio)->firstOrFail();
        dd($proyecto->notificacion()->where('receptor',1)->get());
        // JWTAuth::user()->id
        // $notificacion = Notificaciones::where
        // $notificacion->respuesta = $request->response;
        // $notificacion->save();
        // $this->aceptarAsesor($notificacion);
        // $this->aceptarAlumno($notificacion);
        // $notificaciones_pendientes = Notificaciones::where('proyecto_id', $notificacion->proyecto_id)->where('respuesta', 0)->orWhere('respuesta', null)->count();
        // $proyecto = new Proyectos();
        // $proyecto = $notificacion->proyecto()->first();

        // if ($notificaciones_pendientes == 0) {
        //     $proyecto->aceptado = true;
        // } else {
        //     $proyecto->aceptado = false;
        // }
        // $proyecto->save();
        // if ($notificacion->respuesta)
        //     return back()->with('success', 'Proyecto aceptado.');
        // else
        //     return back()->with('error', 'Proyecto rechazado.');
    }
}

