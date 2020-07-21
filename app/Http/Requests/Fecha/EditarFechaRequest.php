<?php

namespace App\Http\Requests\Fecha;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
use App\Fechas_Foros;

class EditarFechaRequest extends FormRequest
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
        $fecha = Fechas_Foros::Where('fecha',$this->fecha)->firstOrFail();
        return [
            //
            'fecha'     => 'required|unique:fechas_foros,fecha,'.$fecha->id.'|date|after_or_equal:' . Carbon::now()->toDateString(), '|unique',
            'hora_inicio'  => 'required',
            'hora_termino'   => 'required|after:hora_inicio'
        ];
    }
}
