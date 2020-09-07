<?php

namespace App\Http\Requests\Usuario;

use App\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Route;
class EditarUsuarioRequest extends FormRequest
{
    protected $route;
    public function __construct(Route $route)
    {
        $this->route = $route;        
    }
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
        
        // dd($this->input('num_control'));
        // dd(json_decode($this->usuario));
        $user = User::Buscar($this->usuario)->firstOrFail();             
        // dd($user);
        return [
            //
            'num_control' => 'unique:users,num_control,'.$user->id,
            'email' => 'required|unique:users,email,'.$user->id,            
            // 'num_control' => ['required',Rule::unique('users')->ignore($user->id)]
            // 'num_control' => ['required',Rule::unique('users')->ignore($this->usuario)]
        ];
    }
}
