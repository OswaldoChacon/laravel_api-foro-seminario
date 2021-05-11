<?php

namespace App\Http\Requests\Foro;

use Illuminate\Foundation\Http\FormRequest;

class ConfigForoRequest extends FormRequest
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
            'lim_alumnos' => 'required|numeric|min:1',
            'num_aulas' => 'required|numeric|min:1',
            'duracion' => 'required|numeric|min:15',
            'num_maestros' => 'required|numeric|min:2'
        ];
    }
}
