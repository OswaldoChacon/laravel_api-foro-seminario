<?php

namespace App\Http\Requests\Usuario;

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
        return [
            //
            'nombre'=>'required',
            'apellidoP'=>'required',
            'apellidoM' => 'required',
            'email'=>'required|unique:users,email',
            'num_control'=>'required|unique:users,num_control',
            // 'password'=>'required'
        ];
    }
}
