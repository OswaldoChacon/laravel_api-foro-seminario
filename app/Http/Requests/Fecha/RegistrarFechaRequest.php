<?php

namespace App\Http\Requests\Fecha;

use Carbon\Carbon;
use App\FechaForo;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarFechaRequest extends FormRequest
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
                'fecha'     => 'required|unique:fechas_foros,fecha|date|after_or_equal:' . Carbon::now()->toDateString(),
                'hora_inicio'  => 'required|date_format:H:i',
                'hora_termino'   => 'required|date_format:H:i|after:hora_inicio',
                'slug'=>'exists:foros,slug'
            ];
        } else if ($this->getMethod() == 'PUT') {
            $fecha = FechaForo::Where('fecha', $this->fecha)->firstOrFail();
            return [                
                'fecha'     => 'required|unique:fechas_foros,fecha,' . $fecha->id . '|date|after_or_equal:' . Carbon::now()->toDateString(), '|unique',
                'hora_inicio'  => 'required|date_format:H:i',
                'hora_termino'   => 'required|after:hora_inicio',
                'slug'=>'exists:foros,slug'
            ];
        }
    }
}
