<?php

namespace App\Http\Controllers;

use App\Foro;
use App\Receso;
use App\Horario;
use App\FechaForo;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fecha\BreakRequest;
use App\Http\Requests\Fecha\FechaForoRequest;


class FechaForoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FechaForoRequest $request, Foro $foro, FechaForo $fechaForo)
    {
        $this->authorize('create', [$fechaForo, $foro]);
        $fechaForo->fill($request->all());
        $fechaForo->foro()->associate($foro)->save();
        Horario::truncate();
        return response()->json(['message' => 'Registro creado'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(FechaForo $fechaForo)
    {
        return response()->json($fechaForo, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(FechaForoRequest $request, Foro $foro, FechaForo $fechaForo)
    {
        // checar QUE PASA CON LOS HORARIOS DE LOS DOCENTES
        $this->authorize('update', [$fechaForo, $foro]);
        $fechaForo->update($request->all());
        Horario::truncate();
        return response()->json(['Success' => 'Fecha actualizada'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Foro $foro, FechaForo $fechaForo)
    {
        // politicas
        $fechaForo->delete();
        return response()->json(['Success' => 'Fecha eliminada']);
    }

    public function agregarBreak(BreakRequest $request, $fecha)
    {
        // politicas
        $fecha = FechaForo::Where('fecha', $fecha)->first();
        if (is_null($fecha))
            return response()->json(['message' => 'Fecha no encontrada'], 404);
        $foro = $fecha->foro;
        if (is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        if (!$foro->inTime())
            return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        $receso = new Receso();
        $receso->fill($request->all());
        $receso->fecha_foro()->associate($fecha);
        DB::table('horarios')->where('posicion', $request->posicion)->delete();
        $receso->save();
        return response()->json(['mensaje' => 'Receso agregado'], 200);
    }
    public function eliminarBreak(BreakRequest $request, $fecha)
    {
        // politicas
        $fecha = FechaForo::Where('fecha', $fecha)->first();
        if (is_null($fecha))
            return response()->json(['message' => 'Fecha no encontrada'], 404);
        $foro = $fecha->foro()->first();
        if (is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        $receso = Receso::Where([
            ['fecha_foro_id', $fecha->id],
            ['posicion', $request->posicion]
        ])->firstOrFail();
        $receso->delete();
        return response()->json(['mensaje' => 'Break eliminado'], 200);
    }
}
