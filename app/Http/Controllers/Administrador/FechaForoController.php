<?php

namespace App\Http\Controllers\Administrador;

use App\Foro;
use App\FechaForo;
use App\Receso;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fecha\BreakRequest;
use App\Http\Requests\Fecha\RegistrarFechaRequest;


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
    public function store(RegistrarFechaRequest $request)
    {        
        $foro = Foro::Buscar($request->slug)->first();        
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        if(!$foro->inTime())
            return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        $fecha = new FechaForo();
        $fecha->fill($request->all());
        $fecha->foro()->associate($foro);        
        $fecha->save();
        return response()->json(['message' => 'Fecha registrada'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($fecha)
    {
        $fecha = FechaForo::Where('fecha', $fecha)->first();
        if(is_null($fecha))
            return response()->json(['message' => 'Fecha no encontrada'], 404);
        return response()->json($fecha, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RegistrarFechaRequest $request, $fecha)
    {
        $fecha = FechaForo::Where('fecha', $fecha)->first();
        if(is_null($fecha))
            return response()->json(['message' => 'Fecha no encontrada'], 404);
        $foro = $fecha->foro()->first();
        if(is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        if(!$foro->inTime())
            return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        $fecha->fill($request->all());
        $fecha->save();
        return response()->json(['Success' => 'Fecha actualizada'],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($fecha)
    {
        $fecha = FechaForo::Where('fecha', $fecha)->first();
        if(is_null($fecha))
            return response()->json(['message' => 'Fecha no encontrada'], 404);
        // dd($fecha->has('receso')->delete(),$fecha->receso()->get()->flatten());        
        $fecha->delete();
        return response()->json(['Success' => 'Fecha eliminada']);
    }
    public function agregar_break(BreakRequest $request, $fecha)
    {
        $fecha = FechaForo::Where('fecha', $fecha)->first();
        if(is_null($fecha))
            return response()->json(['message' => 'Fecha no encontrada'], 404);
        $foro = $fecha->foro()->first();
        if(is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        if(!$foro->inTime())
            return response()->json(['message' => 'Foro fuera de tiempo'], 400);
        $receso = new Receso();
        $receso->fill($request->all());
        $receso->fechas_foros_id = $fecha->id;
        DB::table('horario_jurado')->where('posicion', $request->posicion)->delete();
        $receso->save();
        return response()->json(['mensaje' => 'Receso agregado'], 200);
    }
    public function eliminar_break(BreakRequest $request, $fecha)
    {
        $fecha = FechaForo::Where('fecha', $fecha)->first();
        if(is_null($fecha))
            return response()->json(['message' => 'Fecha no encontrada'], 404);
        $foro = $fecha->foro()->first();
        if(is_null($foro))
            return response()->json(['message' => 'Foro no encontrado'], 404);        
        if (!$foro->activo)
            return response()->json(['message' => 'Foro inactivo'], 400);
        $receso = Receso::Where([
            ['fechaforo_id', $fecha->id],
            ['posicion', $request->posicion]
        ])->firstOrFail();
        $receso->delete();
        return response()->json(['mensaje' => 'Break eliminado'], 200);
    }
}
