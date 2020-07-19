<?php

namespace App\Http\Requests\Foro;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Foros;
class ForoRequest extends FormRequest
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
            'no_foro' => 'required|numeric|unique:foros,no_foro',
            'nombre' => 'required',
            'periodo' => 'required|unique:foros,periodo,'.$this->id.',id,anio,'.$this->input('anio'),            
            'anio' => 'required|integer|min:' . date('Y') . '|max:' . (date('Y') + 2).',unique:foros,anio,'.$this->id.',id,periodo,'.$this->input('periodo')
        ];
    }
}
