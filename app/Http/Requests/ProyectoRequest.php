<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'titulo' => ['required', 'max:255', Rule::unique('proyectos')->ignore($this->demo)],
            'objetivo' => ['required', 'max:255',Rule::unique('proyectos')->ignore($this->demo)],
            'empresa' => ['required', 'max:255'],
            'linea' => ['required', 'exists:lineas_de_investigacion,clave'],
            'tipo' => ['required', 'exists:tipos_de_proyecto,clave'],
            'asesor' => ['required', 'exists:users,num_control'],
        ];
    }
}
