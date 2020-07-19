<?php

namespace App\Http\Requests;

use App\Rules\ValidarPassword;
use Illuminate\Foundation\Http\FormRequest;
use JWTAuth;
class CambiarPasswordRequest extends FormRequest
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
            'password'=>['required', new ValidarPassword(JWTAuth::user())],
            'nuevo_password'=>'required|different:password|min:7'
        ];
    }
}
