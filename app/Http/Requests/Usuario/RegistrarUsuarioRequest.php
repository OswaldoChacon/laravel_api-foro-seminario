<?php

namespace App\Http\Requests\Usuario;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        return [
            'email' => ['required', Rule::unique('users')->ignore($this->usuario)],
            'num_control' => ['required', Rule::unique('users')->ignore($this->usuario)],
        ];     
    }
}
