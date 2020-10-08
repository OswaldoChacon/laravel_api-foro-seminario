<?php

namespace App\Http\Controllers;

use App\Foro;
use App\User;
use App\Horario;
use App\FechaForo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HorarioController extends Controller
{
    public function agregarHorarioAll(Request $request)
    {
        $request->validate([
            'fecha' => 'required',
            'fecha["fecha"]' => 'exists:fechas_foros,fecha',
            'num_control' => 'required|exists:users,num_control'
        ]);
        $docente = User::Buscar($request->num_control)->first();
        if (!$docente->hasRole('Docente'))
            return response()->json(['message' => 'El usuario no tiene rol de docente como para asignarle horarios'], 400);
        if (!$docente->esJurado())
            return response()->json(['message' => 'No puedes agregar horario. El docente no es parte de ningún jurado'], 400);
        $fecha = FechaForo::Where('fecha', $request->fecha['fecha'])->first();
        $recesos = $fecha->recesos()->get()->pluck('posicion')->toArray();
        $docente->horarios()->where('fecha_foro_id', $fecha->id)->delete();
        $foro = $fecha->foro;
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        if (!$foro->inTime())
            return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        foreach ($request->fecha['intervalos'] as $intervalo) {
            if (!in_array($intervalo['posicion'], $recesos)) {
                $horario = new Horario();
                $horario->user()->associate($docente);
                $horario->fechaForo()->associate($fecha);
                $horario->hora = $intervalo['hora'];
                $horario->posicion = $intervalo['posicion'];
                $horario->save();
            }
        }
        return response()->json(['mensaje' => 'Horarios agregados'], 200);
    }
    public function eliminarHorarioAll(Request $request, $num_control)
    {
        $request->validate([
            'fecha' => 'exists:fechas_foros,fecha',
        ]);
        $fecha = FechaForo::where('fecha', $request->fecha)->first();
        $foro = $fecha->foro;
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        $docente = User::Buscar($num_control)->first();
        if (is_null($docente))
            return response()->json(['message' => 'Docente no encontrado'], 404);
        $fecha = FechaForo::where('fecha', $request->fecha)->first();
        $docente->horarios()->where('fecha_foro_id', $fecha->id)->delete();
        return response()->json(['mensaje' => 'Horarios eliminados'], 200);
    }
    public function agregarHorario(Request $request)
    {
        $request->validate([
            'fecha' => 'exists:fechas_foros,fecha',
            'num_control' => 'exists:users,num_control'
        ]);
        $docente = User::Buscar($request->num_control)->first();
        if (!$docente->hasRole('Docente'))
            return response()->json(['message' => 'El usuario no tiene rol de docente como para asignarle horarios'], 400);
        if (!$docente->esJurado())
            return response()->json(['message' => 'No puedes agregar horario. El docente no es parte de ningún jurado'], 400);
        $fecha = FechaForo::Where('fecha', $request->fecha)->first();
        $horario = new Horario();
        $horario->fill($request->all());
        $horario->user()->associate($docente);
        $horario->fechaForo()->associate($fecha);
        $horario->save();
        return response()->json(['mensaje' => 'Horario agregado'], 200);
    }
    public function eliminarHorario(Request $request, $num_control)
    {
        $request->validate([
            'fecha' => 'exists:fechas_foros,fecha'
        ]);
        $fecha = FechaForo::Where('fecha', $request->fecha)->first();
        $foro = $fecha->foro;
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        $docente = User::Buscar($num_control)->first();
        if (is_null($docente))
            return response()->json(['message' => 'Docente no encontrado'], 404);
        $horario = Horario::Where([
            ['posicion', $request->posicion],
            ['user_id', $docente->id],
            ['fecha_foro_id', $fecha->id]
        ])->firstOrFail();
        $horario->delete();
        return response()->json(['mensaje' => 'Horario eliminado'], 200);
    }
}
