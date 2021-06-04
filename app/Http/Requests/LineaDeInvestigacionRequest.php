<?php

namespace App\Http\Requests\Linea;

use App\LineaDeInvestigacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LineaDeInvestigacionRequest extends FormRequest
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
            'clave' => ['required', Rule::unique('lineas_de_investigacion')->ignore($this->linea)],
            'nombre' => ['required', Rule::unique('lineas_de_investigacion')->ignore($this->linea)]
        ];
    }
}
