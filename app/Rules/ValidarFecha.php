<?php

namespace App\Rules;

use App\FechaForo;
use Mockery\Undefined;
use Illuminate\Contracts\Validation\Rule;

class ValidarFecha implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($periodo, $anio)
    {
        //
        $this->periodo = $periodo;
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
        $meses = array("ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE");
        $mesesPeriodo = explode('-', $this->periodo);
        $fechaLimite = explode('-', $value);
        if ((array_search($mesesPeriodo[0], $meses) + 1) <= $fechaLimite[1] && $fechaLimite[1] <= array_search($mesesPeriodo[1], $meses) + 1 && $this->anio == $fechaLimite[0])
            return true;
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'La fecha asignada esta fuera del periodo establecido';
    }
}
