<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidarPeriodo implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($anio)
    {
        //
        $this->anio = $anio;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        //
        if ($this->anio == date('Y')) {
            $meses = array("ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE");
            $mesesPeriodo = explode('-', $value);
            $mesActual = date('m');
            if ((array_search($mesesPeriodo[0], $meses) + 1) < $mesActual && $mesActual <= (array_search($mesesPeriodo[1], $meses) + 1))
                return true;
            else return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'El periodo seleccionado esta fuera de tiempo';
    }
}
