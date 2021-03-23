<?php

namespace App\Http\Controllers;

use App\Grupo;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
 
    public function grupos(Request $request, $id)
    {
        $GrupoTable = Grupo::query();
        if ($request->nombre){
            $GrupoTable->where('nombre', 'like', '%' . $request->nombre . '%')
                       ->where('plantilla_id', $id);
        }
        $GrupoTable = Grupo::where('plantilla_id', $id);
        $grupos = $GrupoTable->paginate(7);
        return response()->json(['grupos' => $grupos, 'plantilla_id' => $id], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
    {;
        $Grupo = new Grupo;
        $Grupo->fill($request->all())->save();
        return response()->json(['message' => 'Grupo creado'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
