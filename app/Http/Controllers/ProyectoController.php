<?php

namespace App\Http\Controllers;

use App\Foro;
use App\Http\Requests\ProyectoRequest;
use App\LineaDeInvestigacion;
use App\Notificacion;
use App\Proyecto;
use App\TipoDeProyecto;
use App\TipoDeSolicitud;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class ProyectoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return response()->json(Proyecto::paginate($request->porPagina), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProyectoRequest $request, Proyecto $proyecto)
    {
        // $this->authorize('update', $proyecto);
        try {
            DB::beginTransaction();
            $usuarioLogueado = JWTAuth::user();
            $proyecto->fill($request->all());
            $foro = Foro::Activo(true)->first();
            $proyecto->folio = $proyecto->getFolio($foro);
            $proyecto->asesor()->associate(User::Buscar($request->asesor)->first());
            $proyecto->linea_de_investigacion()->associate(LineaDeInvestigacion::where('clave', $request->linea)->first());
            $proyecto->tipo_de_proyecto()->associate(TipoDeProyecto::where('clave', $request->tipo)->first());
            $proyecto->foro()->associate($foro)->save();

            $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->first();
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
            DB::commit();
            return response()->json(['mensaje' => 'Proyecto registrado'], 201);
        } catch (\Exception $exception) {
            DB::rollback();
            return response()
                ->json([
                    'message' => 'Algo mal ha ocurrido'
                ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Proyecto $proyecto)
    {
        return response()->json($proyecto, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProyectoRequest $request, Proyecto $proyecto)
    {
        try {
            $proyecto->fill($request->all());
            $proyecto->asesor()->associate(User::Buscar($request->asesor)->first());
            $proyecto->linea_de_investigacion()->associate(LineaDeInvestigacion::where('clave', $request->linea)->first());
            $proyecto->tipo_de_proyecto()->associate(TipoDeProyecto::where('clave', $request->tipo)->first())->save();
            // $proyecto->foro()->associate($foro)->save();
            $solicitud = TipoDeSolicitud::where('nombre_', 'Registro de proyecto')->first();
            $asesores = $proyecto->notificaciones()->ReceptorConRol('Docente')->where('tipo_de_solicitud_id', $solicitud->id)->get()->pluck('receptor_id')->toArray();
            if (!$proyecto->aceptado) {
                $asesor = User::Buscar($request->asesor)->first();
                if (in_array($asesor->id, $asesores))
                    return response()->json(['message' => 'El asesor que has elegido ya ha rechazado tu solicitud. Elige a otro asesor o espera a que acepte tu solicitud'], 400);
                $proyecto->asesor()->associate($asesor);
            }
            if (!$proyecto->aceptado) {
                $id_s = $proyecto->getIdsNotificaciones();
                Notificacion::whereIn('id', $id_s)->update([
                    'respuesta' => null
                ]);
            }
            DB::commit();
            return response()->json(['mensaje' => 'Proyecto actualizado'], 200);
        } catch (\Exception $exception) {
            DB::rollback();
            return response()
                ->json([
                    'message' => 'Algo mal ha ocurrido',
                    'mensaje' => $exception->getMessage()
                ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Proyecto $proyecto)
    {
        // agregar politica
        $proyecto->delete();
        return response()->json(['mensaje' => 'Proyecto eliminado'], 200);
    }
}
