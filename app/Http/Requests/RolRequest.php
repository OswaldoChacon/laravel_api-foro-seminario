<?php

namespace App\Http\Requests\Rol;

use App\Rol;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RolRequest extends FormRequest
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
            'nombre' => ['required', Rule::unique('roles')->ignore($this->rol)],
            'descripcion' => ['required', Rule::unique('roles')->ignore($this->rol)]
        ];        
    }
}
