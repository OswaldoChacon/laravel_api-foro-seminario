<?php

namespace App\Http\Requests\Linea;

use Illuminate\Foundation\Http\FormRequest;


class RegistroLineaRequest extends FormRequest
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
            'clave' => 'required|unique:lineasdeinvestigacion,clave',
            'nombre' => 'required|unique:lineasdeinvestigacion,nombre'
            //
        ];        
    }
    public function messages()
    {

        return [
            'clave.required' => 'El campo clave es requerido',
            'clave.unique' => 'La clave ingresada ya existe',
            'nombre.required' => 'El campo nombre es requerido',
            'nombre.unique' => 'El nombre ingresado ya existe'
        ];
    }
}
