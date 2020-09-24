<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\User;
use App\Foro;
use App\Proyecto;
use Carbon\Carbon;
use App\Notificacion;
use App\TipoDeProyecto;
use App\TipoDeSolicitud;
use Illuminate\Http\Request;
use App\LineaDeInvestigacion;
use App\Http\Requests\SolicitudRequest;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\Proyecto\RegistrarProyectoRequest;


class AlumnoController extends Controller
{
    //
    public function __construct()
    {
        // $this->middleware('jwtAuth');
    }

    public function foro_actual()
    {
        $usuario = JWTAuth::user();
        if (!$usuario->validarDatosCompletos())
            return response()->json(['message' => 'Debes completar tus datos para registrar un proyecto'], 400);
        if (!$usuario->hasProject())
            return response()->json(['message' => 'No puedes registrar más proyectos'], 400);
        $foro = Foro::select('no_foro', 'nombre', 'periodo', 'anio', 'lim_alumnos', 'fecha_limite')->activo()->first();
        if (is_null($foro))
            return response()->json(['message' => 'No hay ningún foro en curso'], 404);
        $hoy = Carbon::now()->toDateString();
        if ($hoy > $foro->fecha_limite)
            return response()->json(['message' => 'Estas fuera de tiempo para registrar un proyecto'], 400);
        $foro->lim_alumnos -= 1;

        // if ($usuario->proyectoActual()->enviado) {
        $lineas = LineaDeInvestigacion::all();
        $tipos_proyectos = TipoDeProyecto::all();
        $docentes = User::DatosBasicos()->UsuariosConRol('Docente')->get();
        $alumnos = User::DatosBasicos()->UsuariosConRol('Alumno')->doesntHave('proyectos')->where('num_control', '!=', $usuario->num_control)->get();
        return response()->json(['foro' => $foro, 'lineas' => $lineas, 'tipos' => $tipos_proyectos, 'docentes' => $docentes, 'alumnos' => $alumnos], 200);
    }
    public function lista_alumnos()
    {
        $usuario = JWTAuth::user();
        $foro = Foro::Activo()->first();
        if (is_null($foro))
            return response()->json(['mensaje' => 'No hay ningun foro activo'], 200);
        $hoy = Carbon::now()->toDateString();
        $miProyecto = $usuario->proyectos()->whereHas('foro', function (Builder $query) {
            $query->activo();
        })->first();
        if (is_null($miProyecto))
            return response()->noContent();
        $miEquipo = $miProyecto->integrantes()->DatosBasicos()->where('user_id', '!=', $usuario->id)->get();

        // if($hoy > $foro->fecha_limite || $miProyecto->respuestaAsesor())
        if ($hoy > $foro->fecha_limite || $miProyecto->enviado || $miProyecto->aceptado)
            return response()->json($miEquipo, 200);

        $alumnos = User::DatosBasicos()->UsuariosConRol('Alumno')->where('num_control', '!=', $usuario->num_control)->ConDatosCompletos()->get();

        foreach ($alumnos as $alumno) {
            $alumno->myTeam = $miEquipo->contains('num_control', $alumno->num_control);
        }
        $alumnosOrdenados = $alumnos->sortByDesc('myTeam')->values();
        return response()->json($alumnosOrdenados, 200);
    }
    public function registrar_proyecto(RegistrarProyectoRequest $request)
    {
        // vañidar si tiene todos sus campos completados   
        $usuario = JWTAuth::user();
        if (!$usuario->validarDatosCompletos())
            return response()->json(['message' => 'Debes completar tus datos para registrar un proyecto'], 400);
        $foro = Foro::Activo()->first();
        if (is_null($foro))
            return response()->json(['message' => 'No puedes registrar ningún proyecto. No hay ningún foro activo'], 400);
        $hoy = Carbon::now()->toDateString();
        if ($hoy > $foro->fecha_limite)
            return response()->json(['message' => 'Estas fuera de tiempo para registrar un proyecto'], 400);
        $usuario = JWTAuth::user();
        if (!$usuario->hasProject())
            return response()->json(['message' => 'Tienes un proyecto en curso'], 400);
        $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->firstOrFail();
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);


        $proyecto = new Proyecto();
        $proyecto->fill($request->all());
        $proyecto->asesor()->associate(User::Buscar($request->asesor)->first());
        // $proyecto->asesor_id= 18;
        $proyecto->linea_de_investigacion()->associate(LineaDeInvestigacion::where('clave', $request->linea)->first());
        $proyecto->tipo_de_proyecto()->associate(TipoDeProyecto::where('clave', $request->tipo)->first());
        $proyecto->foro()->associate($foro);
        $proyecto->folio = $proyecto->getFolio($foro);
        $proyecto->save();
        $usuario->proyectos()->attach($proyecto);

        $notificacion = new Notificacion();
        $notificacion->emisor_id = $usuario->id;
        $notificacion->receptor_id = $usuario->id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_de_solicitud_id = $solicitud->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        return response()->json(['mensaje' => 'Proyecto registrado'], 200);
    }

    public function actualizar_proyecto(RegistrarProyectoRequest $request, Proyecto $proyecto)
    {
        // aqui tambien pueden usar postman, validarlo
        $proyecto = Proyecto::Buscar($proyecto->folio)->firstOrFail();
        if ($proyecto->enviado) {
            return response()->json(['message' => 'No puedes editar el proyecto porque ya fue enviado'], 400);
        }
        $foro = $proyecto->foro;
        if (Carbon::now()->toDateString() > $foro->fecha_limite)
            return response()->json(['message' => 'Estas fuera de tiempo para realizar cambios a tu proyecto'], 400);

        $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->firstOrFail();
        $asesores = $proyecto->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Docente');
        })->where('tipo_de_solicitud_id', $solicitud->id)->get()->pluck('receptor_id')->toArray();
        
        $asesor = User::Buscar($request->asesor)->firstOrFail();
        if (in_array($asesor->id, $asesores))
            return response()->json(['message' => 'El asesor que has elegido ya ha rechazado tu solicitud. Elige a otro asesor o espera a que acepte tu petición'], 400);


        $proyecto->fill($request->all());
        // validar si se trata de una actualizacion antes de enviar o cuando ya fue aceptado, agregar los request en el formrequest
        if(!$proyecto->aceptado)
            $proyecto->asesor()->associate($asesor);
        $proyecto->linea_de_investigacion()->associate(LineaDeInvestigacion::where('clave', $request->linea)->firstOrFail());
        $proyecto->tipo_de_proyecto()->associate(TipoDeProyecto::where('clave', $request->tipo)->firstOrFail());
        $id_s = $proyecto->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Alumno');
        })->get()->pluck('id')->flatten();

        //solo
        Notificacion::whereIn('id', $id_s)->update([
            'respuesta' => null
        ]);
        $proyecto->save();

        return response()->json(['message' => 'Datos del proyecto actualizado'], 200);
    }

    public function cancelar_solicitud($folio)
    {
        $proyecto = Proyecto::Buscar($folio)->firstOrFail();
        if ($proyecto->aceptado)
            return response()->json(['message' => 'El proyecto ya fue aceptado'], 400);
        $proyecto->enviado = false;
        $proyecto->save();

        $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->firstOrFail();
        $notificacion = $proyecto->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Docente');
        })->where([
            ['tipo_de_solicitud_id', $solicitud->id]
        ])->first();
        if (is_null($notificacion))
            return response()->json(['message' => 'Solicitud cancelada'], 200);
        if ($notificacion->respuesta === false)
            return response()->json(['message' => 'El docente ya ha rechazado el proyecto'], 200);
        $notificacion->delete();
        return response()->json(['message' => 'Solicitud cancelada'], 200);
    }

    public function enviar_solicitud($folio)
    {
        // validar de nuevo que ya todos hayan aceptado        
        $proyecto = Proyecto::Buscar($folio)->firstOrFail();
        if ($proyecto->enviado)
            // return response()->json(['message' => 'El proyecto ya fue notificado al asesor'], 400);
            return response()->json(['message' => 'El proyecto ya fue notificado al asesor'], 400);
        if ($proyecto->aceptado)
            return response()->json(['message' => 'No pudiste notificar al asesor porque el proyecto ya fue aceptado'], 400);
        if (!$proyecto->enviarSolicitud())
            return response()->json(['message' => 'No todos han aceptado o el proyecto ya fue aceptado'], 400);
        $proyecto->enviado = true;

        $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->firstOrFail();

        // verificar que no ya se le haya enviado, advertir que ya lo rechazó
        $asesores = $proyecto->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Docente');
        })->where('tipo_de_solicitud_id', $solicitud->id)->get()->pluck('receptor')->toArray();
        if (in_array($proyecto->asesor, $asesores))
            return response()->json(['message' => 'El asesor que has elegido ya ha rechazado tu solicitud. Elige a otro asesor o espera a que acepte tu petición'], 400);

        $proyecto->save();
        $notificacion = new Notificacion();
        $notificacion->emisor_id = JWTAuth::user()->id;
        $notificacion->receptor_id = $proyecto->asesor_id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_de_solicitud_id = $solicitud->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        // $proyecto->save();
        return response()->json(['message' => 'Solicitud enviada'], 200);
    }
    public function agregar_integrante(Request $request)
    {
        $request->validate([
            'num_control' => 'required|exists:users,num_control'
        ]);
        $usuario = JWTAuth::user();
        $proyecto = $usuario->getProyectoActual();
        if (is_null(($proyecto)))
            return response()->json(['message' => 'No puedes añadir integrantes. No tienes ningún proyecto en curso'], 400);
        if ($proyecto->aceptado)
            return response()->json(['message' => 'No puedes añadir integrantes. El proyecto ya fue aceptado'], 400);
        if ($proyecto->enviado)
            return response()->json(['message' => 'No puedes añadir integrantes. El proyecto esta en proceso de aceptación por el asesor'], 400);
        $foro = $proyecto->foro;
        if ($proyecto->integrantes()->count() >= $foro->lim_alumnos)
            return response()->json(['message' => 'No puedes agregar más integrantes. Tu equipo esta al limite de lo permitido'], 400);
        $integrante = User::Buscar($request->num_control)->first();
        if (!$integrante->validarDatosCompletos())
            return response()->json(['message' => 'Los datos el alumno no estan completos'], 400);
        if (!$integrante->hasProject())
            return response()->json(['message' => 'El alumno que desea agregar ya cuenta con un proyecto'], 400);

        $solicitud = TipoDeSolicitud::where('nombre_', 'registro de proyecto')->first();
        $integrantes = $proyecto->notificaciones()->whereHas('receptor.roles', function (Builder $query) {
            $query->where('nombre_', 'Alumno');
        })->where('tipo_de_solicitud_id', $solicitud->id)->get()->pluck('receptor_id')->toArray();
        if (in_array($request->num_control, $integrantes))
            return response()->json(['message' => 'El integrante que deseas agregar ya ha rechazado tu solicitud'], 400);

        $integrante->proyectos()->attach($proyecto);
        $notificacion = new Notificacion();
        $notificacion->emisor_id = $usuario->id;
        $notificacion->receptor_id = $integrante->id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_de_solicitud_id = TipoDeSolicitud::where('nombre_', 'REGISTRO DE PROYECTO')->firstOrFail()->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        return response()->json(['mensaje' => 'Integrante añadido'], 200);
    }
    public function eliminar_integrante(Request $request)
    {
        $request->validate([
            'num_control' => 'required|exists:users,num_control'
        ]);
        $usuario = JWTAuth::user();
        $proyecto = $usuario->getProyectoActual();
        if (is_null(($proyecto)))
            return response()->json(['message' => 'No puedes añadir integrantes. No tienes ningún proyecto en curso'], 400);
        $integrante = User::Buscar($request->num_control)->firstOrFail();

        // aqui falla porque si quieren eliminar al que solicito no lo encuentra porque el no tienen niguna notificacion
        // $notificacion = $integrante_eliminar->misNotificaciones()->where('proyecto_id', $proyecto->id)->firstOrFail();
        $notificacion = $integrante->misNotificaciones()->where('proyecto_id', $proyecto->id)->first();
        // if($integrante_eliminar->miSolicitud()->where('proyecto_id',$proyecto->id))

        if (!is_null($notificacion)) {
            if ($notificacion->respuesta)
                return response()->json(['message' => 'No puedes eliminar al integrante cuando ya ha aceptado la solicitud.'], 400);
            $notificacion->delete();
        }
        $integrante->proyectos()->detach($proyecto);
        return response()->json(['mensaje' => 'Integrante eliminado'], 200);
    }
    public function actualizar_info(Request $request, $user_id)
    {
        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser(JWTAuth::getToken());
        dd($user->id);
        $user = User::findOrFail($user_id);
        $user->fill($request->only('prefijo'));
        $this->authorize($user);
        $user->save();
    }

    public function get_registrar_solicitud(Request $request)
    {
        $usuario = JWTAuth::user();
        $proyectoEnCurso = $usuario->getProyectoActual();
        if (is_null($proyectoEnCurso))
            return response()->json(['message' => 'No tienes ningun proyecto en curso'], 404);
        if (Carbon::now()->toDateString() <= $proyectoEnCurso->foro->fecha_limite)
            return response()->json(['message' => 'No puedes registrar ninguna solicitud por el momento. La fecha de registro sigue en curso'], 400);
        $solicitudes = TipoDeSolicitud::where('nombre_', '!=', 'REGISTRO DE PROYECTO')->get();
        $docentes = User::UsuariosConRol('Docente')->where('num_control', '!=', $proyectoEnCurso->asesor_id)->get();

        // $docentes = $docentes->filter(function ($docente) use ($proyectoEnCurso) {
        //     return $docente->id !== $proyectoEnCurso->first()->asesor_id;
        // })->values();
        return response()->json(['solicitudes' => $solicitudes, 'docentes' => $docentes, 'proyecto' => $proyectoEnCurso->first()], 200);
    }
    public function registrar_solicitud(SolicitudRequest $request)
    {
        $usuario = JWTAuth::user();
        $proyectoEnCurso = $usuario->getProyectoActual();
        if (Carbon::now()->toDateString() <= $proyectoEnCurso->foro()->first()->fecha_limite)
            return response()->json(['message' => 'No puedes registrar ninguna solicitud durante el periodo de registro'], 400);
        $administrador = User::UsuariosConRol('Administrador')
            // whereHas('roles', function (Builder $query) {
            //     $query->where('nombre_', 'Administrador');
            // })
            ->firstOrFail();

        // tambien validar si otro integrante quiere registrar
        $solicitudesPendientes = $usuario->miSolicitud()->whereHas('tipo_solicitud', function (Builder $query) use ($request) {
            $query->where('nombre_', $request->tipo_solicitud);
        })->where('respuesta', null)->count();

        $notificacionesPendientes = $proyectoEnCurso->first()->notificacion()->where('respuesta', null)->count();

        if ($solicitudesPendientes > 0 || $notificacionesPendientes > 0)
            return response()->json(['message' => 'Tienes pendiente una solicitud similar a la que deseas registrar'], 400);

        // return response()->json($solicitudesPendientes, 200);

        $integrantes = $proyectoEnCurso->first()->integrantes()->where('user_id', '!=', $usuario->id)->get()->pluck('id')->toArray();


        // agregar al asesor en el arreglo de integrantes 
        // array_push($integrantes, $proyectoEnCurso->first()->asesor);        

        $solicitud = TipoDeSolicitud::where('nombre_', $request->tipo_solicitud)->firstOrFail();

        $nuevo_titulo = null;
        $titulo_anterior = null;
        $nuevo_asesor = null;
        $anterior_asesor = null;
        switch ($solicitud->nombre_) {
            case 'CANCELACION DEL PROYECTO':
                break;
            case 'CAMBIO DE TITULO DEL PROYECTO':
                $request->validate(['nuevo_titulo' => 'required']);
                $nuevo_titulo = $request->nuevo_titulo;
                break;
            case 'DAR DE BAJA A UN INTEGRANTE':
                break;
            case 'CAMBIO DE ASESOR':
                $request->validate(['nuevo_asesor' => 'required|exists:users,num_control']);
                $nuevo_asesor = User::Buscar($request->nuevo_asesor)->first()->id;
                break;
        }
        $notificaciones = [];
        // return response()->json($notificaciones, 200);
        foreach ($integrantes as $integrante) {
            $notificaciones[] = [
                'emisor' => $usuario->id,
                'receptor' => $integrante,
                'administrador' => 0,
                'proyecto_id' => $proyectoEnCurso->first()->id,
                'tipo_solicitud' => $solicitud->id,
                'nuevo_asesor' => $nuevo_asesor,
                'nuevo_titulo' => $nuevo_titulo,
                'motivo' => $request->motivo,
                'fecha' => Carbon::now()->toDateString()
            ];
        }

        // solicitud del admin
        $notificaciones[] = [
            'emisor' => $usuario->id,
            'receptor' => $administrador->id,
            'administrador' => 1,
            'proyecto_id' => $proyectoEnCurso->first()->id,
            'tipo_solicitud' => $solicitud->id,
            'nuevo_asesor' => $nuevo_asesor,
            'nuevo_titulo' => $request->nuevo_titulo,
            'motivo' => $request->motivo,
            'fecha' => Carbon::now()->toDateString()
        ];

        Notificacion::insert($notificaciones);

        return response()->json(['message' => 'Solicitud registrada'], 200);
    }
}
