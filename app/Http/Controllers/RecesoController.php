<?php

namespace App\Http\Controllers;

use App\FechaForo;
use App\Foro;
use App\Http\Requests\Fecha\RecesoRequest;
use App\Receso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecesoController extends Controller
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
    public function store(RecesoRequest $request, Foro $foro, FechaForo $fechaForo, Receso $receso)
    {
        $this->authorize('create', [$receso, $fechaForo]);
        $receso->fill($request->all());
        $receso->fecha_foro()->associate($fechaForo);
        DB::table('horarios')->where('posicion', $request->posicion)->delete();
        $receso->save();
        return response()->json(['mensaje' => 'Registro creado'], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Receso $receso)
    {
        return response()->json($receso, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RecesoRequest $request, Foro $foro, FechaForo $fechaForo, Receso $receso)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Foro $foro, FechaForo $fechaForo, Receso $receso)
    {
        $this->authorize('delete', [$receso, $foro]);
        $receso = Receso::Where([
            ['fecha_foro_id', $fechaForo->id],
            ['posicion', $request->posicion]
        ])->firstOrFail();
        $receso->delete();
        return response()->json(['mensaje' => 'Registro eliminado'], 200);
    }
}
