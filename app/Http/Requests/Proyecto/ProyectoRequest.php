<?php

namespace App\Http\Requests\Proyecto;

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
            'titulo'=>'required|unique:proyectos,titulo|max:255',
            'objetivo'=>'required|max:255',
            'empresa'=>'required|max:255',
            'linea'=>'required',
            'tipo'=>'required',
            'asesor'=>'required',
        ];
    }
}
