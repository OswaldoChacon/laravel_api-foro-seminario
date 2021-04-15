<?php

namespace App\Http\Requests\Tipos;

use App\TipoDeProyecto;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarTiposRequest extends FormRequest
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
                'clave' => 'required|unique:tipos_de_proyecto,clave',
                'nombre' => 'required|unique:tipos_de_proyecto,nombre'
            ];
        } else if ($this->getMethod() == 'PUT') {            
            return [
                'clave' => 'required|unique:tipos_de_proyecto,clave,' . $this->tiposProyecto->id,
                'nombre' => 'required|unique:tipos_de_proyecto,nombre,' . $this->tiposProyecto->id
            ];
        }
    }
}
