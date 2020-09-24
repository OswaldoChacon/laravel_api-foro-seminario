<?php

namespace App\Http\Requests\Solicitud;

use App\TipoDeSolicitud;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarSolicitudRequest extends FormRequest
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
                'nombre_' => 'required|unique:tipos_de_solicitud,nombre_'
            ];
        } elseif ($this->getMethod() == 'PUT') {            
            return [
                'nombre_' => 'required|unique:tipos_de_solicitud,nombre_,'.$this->solicitude->id
            ];
        }
    }
}
