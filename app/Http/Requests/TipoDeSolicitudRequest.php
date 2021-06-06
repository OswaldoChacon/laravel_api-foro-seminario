<?php

namespace App\Http\Requests;

use App\TipoDeSolicitud;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoDeSolicitudRequest extends FormRequest
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
            'nombre' => ['required', Rule::unique('tipos_de_solicitud')->ignore($this->solicitud)],
            'descripcion' => ['required', Rule::unique('tipos_de_solicitud')->ignore($this->solicitud)]
        ];
    }
}
