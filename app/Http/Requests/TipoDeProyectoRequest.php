<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoDeProyectoRequest extends FormRequest
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
            'clave' => ['required', Rule::unique('tipos_de_proyecto')->ignore($this->tipoDeProyecto)],
            'nombre' => ['required', Rule::unique('tipos_de_proyecto')->ignore($this->tipoDeProyecto)]
        ];
    }
}
