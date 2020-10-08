<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerarHorarioRequest extends FormRequest
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
            'alpha' => 'required|numeric|max:1|not_in:0',
            'beta' => 'required|numeric|not_in:0',
            'Q' => 'required',
            'evaporation' => 'required',
            'iterations' => 'required',
            'ants' => 'required|numeric|min:2',
            'estancado' => 'required|numeric|not_in:0',
            // 't_max' => 'required|numeric|not_in:0',
            't_minDenominador' => 'required|numeric|not_in:0'
        ];
    }
}
