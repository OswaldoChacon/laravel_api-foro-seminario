<?php

namespace App\Http\Requests\Fecha;

use App\Foro;
use App\FechaForo;
use Carbon\Carbon;
use App\Rules\ValidarFecha;
use App\Rules\ValidarHora;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FechaForoRequest extends FormRequest
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
            'fecha'     => [
                'required', Rule::unique('fechas_foros')->ignore($this->fechaForo), 'date', 'after_or_equal:' . Carbon::now()->toDateString(),
                // new ValidarFecha($this->foro->periodo, $this->foro->anio)
            ],
            'hora_inicio'  => ['required','date_format:H:i'],
            'hora_termino'   => ['required', 'date_format:H:i', 'after:hora_inicio', new ValidarHora($this->input('hora_inicio'), $this->input('hora_termino'), $this->foro)],
        ];
    }
}
