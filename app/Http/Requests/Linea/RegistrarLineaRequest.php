<?php

namespace App\Http\Requests\Linea;

use App\LineaDeInvestigacion;
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
                'clave' => 'required|unique:lineas_de_investigacion,clave',
                'nombre' => 'required|unique:lineas_de_investigacion,nombre',
            ];
        } else if ($this->getMethod() == 'PUT') {
            return [
                'clave' => 'required|unique:lineas_de_investigacion,clave,' . $this->linea->id,
                'nombre' => 'required|unique:lineas_de_investigacion,nombre,' . $this->linea->id
            ];
        }
    }
}
