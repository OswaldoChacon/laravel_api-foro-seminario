<?php

namespace App\Http\Requests\Linea;

use App\LineasDeInvestigacion;
use Illuminate\Foundation\Http\FormRequest;


class RegistrarLineaRequest extends FormRequest
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
                'clave' => 'required|unique:lineasdeinvestigacion,clave',
                'nombre' => 'required|unique:lineasdeinvestigacion,nombre'
                //
            ];
        } else if ($this->getMethod() == 'PUT') {
            $linea = LineasDeInvestigacion::Where('clave', $this->linea)->firstOrFail();
            return [
                'clave' => 'required|unique:lineasdeinvestigacion,clave,' . $linea->id,
                'nombre' => 'required|unique:lineasdeinvestigacion,nombre,' . $linea->id
            ];
        }
    }
}
