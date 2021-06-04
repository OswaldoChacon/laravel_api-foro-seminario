<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Foro;
use App\User;
use App\Proyecto;
use Carbon\Carbon;
use App\Notificacion;
use App\TipoDeProyecto;
use App\TipoDeSolicitud;
use Illuminate\Http\Request;
use App\LineaDeInvestigacion;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use App\Http\Requests\ProyectoRequest;

class ProyectoController extends Controller
{
    public function registrarProyecto(ProyectoRequest $request)
    {
        $usuarioLogueado = JWTAuth::user();
        if (!$usuarioLogueado->validarDatosCompletos())
            return response()->json(['message' => 'Debes completar tus datos para registrar un proyecto'], 400);
        $foro = Foro::Activo(true)->first();
        if (is_null($foro))
            return response()->json(['message' => 'No puedes registrar ningún proyecto. No hay ningún foro activo'], 400);
        // if (Carbon::now()->toDateString() > $foro->fecha_limite)
        //     return response()->json(['message' => 'Estas fuera de tiempo para registrar un proyecto'], 400);
        if (!$usuarioLogueado->hasProject())
            return response()->json(['message' => 'Tienes un proyecto en curso o ya has aprobado'], 400);
        $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->first();
        if (is_null($solicitud))
            return response()->json(['message' => 'Tipo de solicitud no encontrada'], 404);
        $proyecto = new Proyecto();
        $proyecto->fill($request->all());
        $proyecto->asesor()->associate(User::Buscar($request->asesor)->first());
        $proyecto->linea_de_investigacion()->associate(LineaDeInvestigacion::where('clave', $request->linea)->first());
        $proyecto->tipo_de_proyecto()->associate(TipoDeProyecto::where('clave', $request->tipo)->first());
        $proyecto->foro()->associate($foro);
        $proyecto->folio = $proyecto->getFolio($foro);
        $proyecto->save();
        $usuarioLogueado->proyectos()->attach($proyecto);

        $total = Notificacion::all()->unique('folio')->count();
        $folio = 'RS-' . ($total + 1);
        $notificacion = new Notificacion();
        $notificacion->folio = $folio;
        $notificacion->emisor_id = $usuarioLogueado->id;
        $notificacion->receptor_id = $usuarioLogueado->id;
        $notificacion->proyecto_id = $proyecto->id;
        $notificacion->tipo_de_solicitud_id = $solicitud->id;
        $notificacion->fecha = Carbon::now()->toDateString();
        $notificacion->save();
        return response()->json(['mensaje' => 'Proyecto registrado'], 200);
    }

    public function actualizarProyecto(ProyectoRequest $request, Proyecto $proyecto)
    {
        $usuarioLogueado = JWTAuth::user();
        $proyecto = Proyecto::Buscar($proyecto->folio)->firstOrFail();
        if (!$usuarioLogueado->esMiProyecto($proyecto))
            return response()->json(['message' => 'No puedes editar el proyecto ya que no perteneces a él'], 400);
        if ($proyecto->enviado && !$proyecto->aceptado)
            return response()->json(['message' => 'No puedes editar el proyecto porque ya fue enviado'], 400);
        $foro = $proyecto->foro;
        if (Carbon::now()->toDateString() > $foro->fecha_limite)
            return response()->json(['message' => 'Estas fuera de tiempo para realizar cambios a tu proyecto'], 400);
        if ($proyecto->aceptado && !$proyecto->permitir_cambios)
            return response()->json(['message' => 'No esta autorizado para actualizar tu proyecto'], 400);
        $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->first();
        $asesores = $proyecto->notificaciones()->ReceptorConRol('Docente')->where('tipo_de_solicitud_id', $solicitud->id)->get()->pluck('receptor_id')->toArray();
        if (!$proyecto->aceptado) {
            $asesor = User::Buscar($request->asesor)->first();
            if (in_array($asesor->id, $asesores))
                return response()->json(['message' => 'El asesor que has elegido ya ha rechazado tu solicitud. Elige a otro asesor o espera a que acepte tu solicitud'], 400);
            $proyecto->asesor()->associate($asesor);
        }

        $proyecto->fill($request->all());
        $proyecto->linea_de_investigacion()->associate(LineaDeInvestigacion::Buscar($request->linea)->first());
        $proyecto->tipo_de_proyecto()->associate(TipoDeProyecto::Buscar($request->tipo)->first());
        $proyecto->permitir_cambios = 0;
        $proyecto->save();
        if (!$proyecto->aceptado) {
            $id_s = $proyecto->getIdsNotificaciones();
            Notificacion::whereIn('id', $id_s)->update([
                'respuesta' => null
            ]);
        }
        return response()->json(['message' => 'Datos del proyecto actualizado'], 200);
    }

    public function eliminarProyecto(Proyecto $proyecto)
    {
        return response()->json($proyecto, 200);
    }

    public function proyectoParticipa(Request $request, $folio)
    {
        $proyecto = Proyecto::Where('folio', $folio)->first();
        if (is_null($proyecto))
            return response()->json(['message' => 'Proyecto no encontrado'], 404);
        if (!$proyecto->aceptado)
            return response()->json(['message' => 'El proyecto no puede participar porque aún no ha sido aceptado'], 400);
        $foro = $proyecto->foro;
        if (!$foro->activo)
            return response()->json(['message' => 'El foro no esta activo para poder actualizar el proyecto'], 400);
        $proyecto->participa = $request->participa;
        $proyecto->save();
        return response()->json(['mensaje' => 'Proyecto actualizado'], 200);
    }

    public function permitirCambios(Request $request, $folio)
    {
        $request->validate([
            'cambios' => 'required|boolean'
        ]);
        $proyecto = Proyecto::Buscar($folio)->firstOrFail();
        $foro = $proyecto->foro;
        if (Carbon::now()->toDateString() > $foro->fecha_limite)
            return response()->json(['message' => 'No se puede editar el proyecto, fuera de tiempo'], 400);
        if (!$proyecto->aceptado)
            return response()->json(['message' => 'No puedes realizar esta acción si el proyecto no ha sido aceptado'], 400);
        $proyecto->permitir_cambios = $request->cambios;
        $proyecto->save();
        $message = $request->cambios ? 'Proyecto habilitado ha cambios' : 'Proyecto deshabilitado ha cambios';
        return response()->json(['message' => $message], 200);
    }
    public function proyectoActual()
    {
        $usuarioLogueado = JWTAuth::user();
        $proyecto = $usuarioLogueado->proyectos()->with(['linea_de_investigacion', 'tipo_de_proyecto', 'asesor'])->whereHas('foro', function (Builder $query) {
            $query->Activo(true);
        })->first();
        if (is_null($proyecto))
            return response()->noContent();
        $proyecto->append('editar', 'enviar', 'cancelar', 'inTime');
        return response()->json($proyecto, 200);
    }

    public function getProyecto(Proyecto $proyecto)
    {
        return response()->json($proyecto, 200);
    }
}
