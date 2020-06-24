<?php

namespace App\Http\Controllers;

use JWTAuth;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProyectoRequest;
use Illuminate\Http\Request;
use App\Proyectos;
use App\Foros;
use App\LineasDeInvestigacion;
use App\TiposProyectos;
use App\User;

class AlumnoController extends Controller
{
    //
    public function __construct()
    {
        // $this->middleware('jwtAuth');
    }

    public function foro_actual(){        
        $foro = Foros::select('no_foro','nombre','periodo','anio','lim_alumnos')->where('acceso',true)->first();
        $foro->lim_alumnos -=1;
        $lineas = LineasDeInvestigacion::all();
        $tipos_proyectos = TiposProyectos::all();
        $docentes = User::select('num_control',DB::raw("CONCAT(prefijo,' ',nombre,' ',apellidoP,' ',apellidoM) AS nombre"))->whereHas('roles',function($query){
            $query->where('roles.nombre','Docente');
        })->get();
        $alumnos = User::select('num_control',DB::raw("CONCAT(nombre,' ',apellidoP,' ',apellidoM) AS nombre"))->whereHas('roles',function($query){
            $query->where('roles.nombre','Alumno');
        })->doesntHave('proyectos')->where('num_control','!=',JWTAuth::user()->num_control)->get();        
        return response()->json(['foro'=>$foro,'lineas'=>$lineas,'tipos'=>$tipos_proyectos,'docentes'=>$docentes,'alumnos'=>$alumnos], 200);
    }
    public function registrar_proyecto(ProyectoRequest $request)
    {    
        // $foro = Foros::findOrFail($foro_id);  
        // dd($request->alumnos);
        $user = JWTAuth::user();
        // dd($user);
        if(!JWTAuth::user()->hasProject())      
            return response()->json(['mensaje'=>'Tienes un proyecto en curso'], 200);
        
        $foro = Foros::where('acceso',true)->firstOrFail();
        // if(!$foro->acceso)
        //     return response()->json(['mensaje'=>'Foro no activo'], 200);
        $receptores = array(); 
        
        array_push($receptores, $request->asesor);           
        for ($i = 0; $i < sizeof($request->alumnos); $i++) {
            if ($request->alumnos[$i] != null) {            
                if (!in_array($request->alumnos[$i], $receptores)) {
                    // $user =                     
                    if(!User::where('num_control',$request->alumnos[$i])->firstOrFail()->hasProject())
                    return response()->json(['mensaje'=>'Uno de tus integrantes ya cuenta con un proyecto registrado'], 422);
                    // return back()->with('error','Uno de tus integrantes ya cuenta con un proyecto registrado');
                    array_push($receptores, $request->alumnos[$i]);                                        
                } else {
                    return response()->json(['mensaje'=>'Has elegido al mismo integrante en más de una ocasión.'], 422);
                    // return back()->with('error', 'Has elegido al mismo integrante en más de una ocasión.');
                }
                // dd("l");
            }
        }

        $proyecto = new Proyectos();
        $folio = $proyecto->folio($foro);
        $proyecto->fill($request->all());
        // $asesor = User::select('id')->where('num_control',$request->asesor)->firstOrFail()->id;
        // dd($asesor);
        $proyecto->asesor = User::where('num_control',$request->asesor)->firstOrFail()->id;
        $proyecto->lineadeinvestigacion_id = LineasDeInvestigacion::where('clave',$request->linea)->firstOrFail()->id;
        // dd($proyecto);
        $proyecto->tipos_proyectos_id = TiposProyectos::where('clave',$request->tipo)->firstOrFail()->id;
        $proyecto->foros_id = $foro->id;
        $proyecto->folio = $folio;            
        // dd($proyecto);
        // $proyecto->asesor = $request->asesor;
        // $proyecto->save();
        return response()->json(['mensaje'=>'Proyecto registrado'], 200);
    }

    public function actualizar_info(Request $request,$user_id)
    {             
        // JWTAuth::toUser($request->token);         
        // $userAuth = JWTAuth::user();
        // dd(JWTAuth::parseToken()->authenticate());
        $token = JWTAuth::getToken();
        // dd($token);
        $user = JWTAuth::toUser(JWTAuth::getToken());
        dd($user->id);
        // dd(JWTAuth::user());
        // dd(JWTAuth::toUser($request->token));
        $user = User::findOrFail($user_id);        
        // $user->email = $request->email;
        // $user->fill($request->only('email'));
        $user->fill($request->only('prefijo'));
        $this->authorize($user);
        $user->save();        
    }
}
