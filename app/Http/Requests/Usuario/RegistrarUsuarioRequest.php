<?php

namespace App\Http\Requests\Usuario;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarUsuarioRequest extends FormRequest
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
            $rules = [
                'email' => 'required|unique:users,email',
                'num_control' => 'required|unique:users,num_control',
            ];
        }
        if ($this->getMethod() == 'PUT') {            
            $rules = [
                'num_control' => 'required|unique:users,num_control,' . $this->usuario->id,
                'email' => 'required|unique:users,email,' . $this->usuario->id,
            ];
        }
        return $rules;
    }
}
