<?php

namespace App\Rules;

use DateTime;
use Illuminate\Contracts\Validation\Rule;

class ValidarHora implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($hora_inicio, $hora_termino, $foro)
    {
        //
        $this->hora_inicio = new DateTime($hora_inicio);
        $this->hora_termino = new DateTime($hora_termino);
        $this->foro = $foro;
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
        $intervalo = $this->hora_inicio->diff($this->hora_termino)->format('%h:%i:%s');
        $explodeHoras = explode(':', $intervalo);
        $totalMinutos = $explodeHoras[0] * 60 + $explodeHoras[1] + $explodeHoras[2] / 60;
        if (($totalMinutos % $this->foro->duracion) === 0)
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
        return 'La hora de termino debe ser multiplo de '. $this->foro->duracion.' con referencia a la hora de inicio';
    }
}
