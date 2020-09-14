<?php

namespace App\Http\Requests\Solicitud;

use App\TiposSolicitud;
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
                'nombre_' => 'required|unique:tipos_solicitud,nombre_'
            ];
        } elseif ($this->getMethod() == 'PUT') {
            $solicitud = TiposSolicitud::where('nombre_', $this->solicitude)->firstOrFail();
            return [
                'nombre_' => 'required|unique:tipos_solicitud,nombre_,'.$solicitud->id
            ];
        }
    }
}
