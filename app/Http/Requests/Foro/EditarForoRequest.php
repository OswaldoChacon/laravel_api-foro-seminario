<?php

namespace App\Http\Requests\Foro;

use App\Foros;
use Illuminate\Foundation\Http\FormRequest;

class EditarForoRequest extends FormRequest
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
        $foro = Foros::Buscar($this->foro)->firstOrFail();
        return [
            //
            'no_foro' => 'required|numeric|unique:foros,no_foro,'.$foro->id,
            'nombre' => 'required',
            'periodo' => 'required|unique:foros,periodo,'.$foro->id.',id,anio,'.$this->input('anio'),
            'anio' => 'required|integer|min:' . date('Y') . '|max:' . (date('Y') + 2).',unique:foros,anio,'.$foro->id.',id,periodo,'.$this->input('periodo')
        ];
    }
}
