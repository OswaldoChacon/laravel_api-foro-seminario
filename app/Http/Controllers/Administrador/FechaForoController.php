<?php

namespace App\Http\Controllers\Administrador;

use App\Foros;
use App\Fechas_Foros;
use App\HorarioBreak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fecha\BreakRequest;
use App\Http\Requests\Fecha\EditarFechaRequest;

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
    public function store(Request $request)
    {
        $fecha = new Fechas_Foros();
        $foro = Foros::Buscar($request->slug)->firstOrFail();
        // $foro = Foros::Where('slug', $request->slug)->firstOrFail();
        if (!$foro->acceso)
            return response()->json(['mensaje' => 'Foro no activo'], 500);
        $fecha->fill($request->all());
        $fecha->foros_id = $foro->id;
        $fecha->save();
        return response()->json(['mensaje' => 'Fecha registrada'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        return response()->json($fecha, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EditarFechaRequest $request, $fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        $fecha->fill($request->all());
        $fecha->save();
        return response()->json(['Success' => 'Fecha actualizada']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        // dd($fecha->has('receso')->delete(),$fecha->receso()->get()->flatten());        
        $fecha->delete();
        return response()->json(['Success' => 'Fecha eliminada']);
    }
    public function agregar_break(BreakRequest $request, $fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        $foro = $fecha->foro()->first();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 422);
        $receso = new HorarioBreak();
        $receso->fill($request->all());
        $receso->fechas_foros_id = $fecha->id;
        DB::table('horario_jurado')->where('posicion', $request->posicion)->delete();
        $receso->save();
        return response()->json(['mensaje' => 'Receso agregado'], 200);
    }
    public function eliminar_break(BreakRequest $request, $fecha)
    {
        $fecha = Fechas_Foros::Where('fecha', $fecha)->firstOrFail();
        $foro = $fecha->foro()->first();
        if (!$foro->acceso)
            return response()->json(['message' => 'Foro no activo'], 422);
        $receso = HorarioBreak::Where([
            ['fechas_foros_id', $fecha->id],
            ['posicion', $request->posicion]
        ])->firstOrFail();
        $receso->delete();
        return response()->json(['mensaje' => 'Break eliminado'], 200);
    }
}
