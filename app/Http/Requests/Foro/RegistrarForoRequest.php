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
        if ($this->getMethod() == 'POST') {
            return [
                'no_foro' => 'required|numeric|unique:foros,no_foro',
                'nombre' => 'required',
                'periodo' => [
                    'required', 'unique:foros,periodo,' . $this->id . ',id,anio,' . $this->input('anio'),
                    // new ValidarPeriodo($this->input('anio'))
                ],
                'anio' => [
                    'required', 'integer',
                    // 'min:' . date('Y'),
                    'max:' . (date('Y') + 2), 'unique:foros,anio,' . $this->id . ',id,periodo,' . $this->input('periodo')
                ],
                'fecha_limite' => ['required', 'date', 
                // 'after_or_equal:' . Carbon::now()->toDateString(), 
                new ValidarFecha($this->input('periodo'), $this->input('anio'))]
            ];
        } else if ($this->getMethod() == 'PUT') {
            return [
                'no_foro' => 'required|numeric|unique:foros,no_foro,' . $this->foro->id,
                'nombre' => 'required',
                'periodo' => ['required', 'unique:foros,periodo,' . $this->foro->id . ',id,anio,' . $this->input('anio'),
                    // new ValidarPeriodo($this->input('anio'))
                ],
                'anio' => [
                    'required', 'integer',
                    // 'min:' . date('Y'), 
                    'max:' . (date('Y') + 2), 'unique:foros,anio,' . $this->foro->id . ',id,periodo,' . $this->input('periodo')
                ],
                'fecha_limite' => ['required', 'date', 
                // 'after_or_equal:' . Carbon::now()->toDateString(), 
                new ValidarFecha($this->input('periodo'), $this->input('anio'))]

            ];
        }
    }
}
