<?php

namespace App\Http\Requests;

use App\Rules\ValidarPonderacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConceptoRequest extends FormRequest
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
            'nombre' => ['required', Rule::unique('conceptos')->ignore($this->concepto)],
            'ponderacion' => ['required', 'numeric', 'min:1', new ValidarPonderacion($this->grupo, $this->concepto, $this->ponderacion)]
        ];
    }
}
