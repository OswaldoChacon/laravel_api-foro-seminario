<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProyectoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'titulo'=>'required|unique:proyectos,titulo',
            'objetivo'=>'required',
            'empresa'=>'required',
            'lineadeinvestigacion_id'=>'required',
            'tipos_proyectos_id'=>'required',
            'asesor'=>'required',
        ];
    }
}
