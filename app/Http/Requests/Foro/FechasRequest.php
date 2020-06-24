<?php

namespace App\Http\Requests\Foro;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
class FechasRequest extends FormRequest
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
            'fecha'     => 'required|unique:fechas_foros,fecha|date|after_or_equal:' . Carbon::now()->toDateString(),
            'hora_inicio'  => 'required|date_format:H:i',
            'hora_termino'   => 'required|date_format:H:i|after:hora_inicio'
        ];
    }
}
