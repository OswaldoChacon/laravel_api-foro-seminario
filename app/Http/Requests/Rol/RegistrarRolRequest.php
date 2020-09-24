<?php

namespace App\Http\Requests\Rol;

use App\Rol;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarRolRequest extends FormRequest
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
                'nombre_' => 'required|unique:roles,nombre_'
            ];
        } elseif ($this->getMethod() == 'PUT') {                        
            return [                
                'nombre_' => 'required|unique:roles,nombre_,'.$this->role->id
            ];
        }
    }
}
