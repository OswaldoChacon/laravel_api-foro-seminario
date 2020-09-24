<?php

namespace App\Http\Requests\Proyecto;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarProyectoRequest extends FormRequest
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
        if ($this->getMethod() == 'POST') {
            return [
                //
                'titulo' => 'required|unique:proyectos,titulo|max:255',
                'objetivo' => 'required|max:255',
                'empresa' => 'required|max:255',
                'linea' => 'required|exists:lineas_de_investigacion,clave',
                'tipo' => 'required|exists:tipos_de_proyecto,clave',
                'asesor' => 'required|exists:users,num_control',
            ];
        } else if ($this->getMethod() == 'PUT') {            
            return [
                'titulo' => 'required|max:255|unique:proyectos,titulo,'.$this->proyecto->id,
                'objetivo' => 'required|max:255',
                'empresa' => 'required|max:255',
                'linea' => 'required|exists:lineas_de_investigacion,clave',
                'tipo' => 'required|exists:tipos_de_proyecto,clave',
                'asesor' => 'required|exists:users,num_control',
            ];
        }
    }
}
