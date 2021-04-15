<?php

namespace App\Http\Requests\Fecha;

use App\Foro;
use App\FechaForo;
use Carbon\Carbon;
use App\Rules\ValidarFecha;
use App\Rules\ValidarHora;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        // if ($this->getMethod() == 'POST') {
        $foro = Foro::Buscar($this->input('slug'))->firstOrFail();
        return [
            // 'fecha'     => 'required|unique:fechas_foros,fecha|date|after_or_equal:' . Carbon::now()->toDateString(),
            'fecha'     => ['required', Rule::unique('fechas_foros')->ignore($this->fechaforo), 'date', 'after_or_equal:' . Carbon::now()->toDateString(), new ValidarFecha($foro->periodo, $foro->anio)],
            'hora_inicio'  => 'required|date_format:H:i',
            'hora_termino'   => ['required', 'date_format:H:i', 'after:hora_inicio', new ValidarHora($this->input('hora_inicio'), $this->input('hora_termino'), $foro)],
            'slug' => 'exists:foros,slug'
        ];
        // } else if ($this->getMethod() == 'PUT') {
        // return [                
        //     'fecha'     => ['required', 'unique:fechas_foros,fecha,' . $this->fechaforo->id, 'date', 'after_or_equal:' . Carbon::now()->toDateString(), new ValidarFecha($this->fechaforo->foro->periodo, $this->fechaforo->foro->anio)],
        //     'hora_inicio'  => 'required|date_format:H:i',
        //     'hora_termino'   => 'required|after:hora_inicio',
        //     'slug' => 'exists:foros,slug'
        // ];
        // }
    }
}
