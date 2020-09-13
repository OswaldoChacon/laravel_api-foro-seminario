<?php

namespace App\Http\Requests\Tipos;

use App\TiposProyectos;
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
                //
                'clave' => 'required|unique:tipos_de_proyectos,clave',
                'nombre' => 'required|unique:tipos_de_proyectos,nombre'
            ];
        } else if ($this->getMethod() == 'PUT') {
            $tipos = TiposProyectos::Where('clave', $this->tiposProyecto)->firstOrFail();
            return [
                'clave' => 'required|unique:tipos_de_proyectos,clave,' . $tipos->id,
                'nombre' => 'required|unique:tipos_de_proyectos,nombre,' . $tipos->id
            ];
        }
    }
}
