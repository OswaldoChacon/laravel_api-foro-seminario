<?php

namespace App\Http\Requests\Linea;

use App\LineasDeInvestigacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;


class EditarLineaRequest extends FormRequest
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
        $linea = LineasDeInvestigacion::Where('clave',$this->linea)->firstOrFail();
        return [
            'clave' => 'required|unique:lineasdeinvestigacion,clave,'.$linea->id,
            'nombre' => 'required|unique:lineasdeinvestigacion,nombre,'.$linea->id
        ];
    }
    public function messages()
    {

        return [
            'clave.required' => 'El campo clave es requerido',
            'clave.unique' => 'La clave ingresada ya existe',
            'nombre.required' => 'El campo nombre es requerido',
            'nombre.unique' => 'El nombre ingresado ya existe'
        ];
    }
}
