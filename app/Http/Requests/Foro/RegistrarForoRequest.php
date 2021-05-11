<?php

namespace App\Http\Requests\Foro;

use App\Foro;
use App\Rules\ValidarFecha;
use App\Rules\ValidarPeriodo;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarForoRequest extends FormRequest
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
            'no_foro' => 'required', 'numeric', Rule::unique('foros')->ignore($this->foro),
            'nombre' => 'required',
            'periodo' => [
                'required',
                Rule::unique('foros')->ignore($this->foro)->where('anio', $this->anio)
                // new ValidarPeriodo($this->input('anio'))
            ],
            'anio' => [
                'required', 'integer',
                // 'min:' . date('Y'),
                'max:' . (date('Y') + 2),
                Rule::unique('foros')->ignore($this->foro)->where('periodo', $this->periodo)
            ],
            'fecha_limite' => [
                'date',
                // 'after_or_equal:' . Carbon::now()->toDateString(), 
                // new ValidarFecha($this->input('periodo'), $this->input('anio'))
            ]
        ];
    }
}
