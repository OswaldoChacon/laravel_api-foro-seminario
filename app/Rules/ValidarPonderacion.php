<?php

namespace App\Rules;

use App\Grupo;
use App\Plantilla;
use Illuminate\Contracts\Validation\Rule;

class ValidarPonderacion implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(Plantilla $plantilla, $grupo, int $ponderacion)
    {
        // $this->grupo = $grupo;
        $this->plantilla = $plantilla;
        $this->ponderacion = $ponderacion;
        $this->grupo = $grupo;
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
        $this->ponderacion += $this->plantilla->grupos->sum('ponderacion') - $this->grupo->ponderacion;        
        if ($this->ponderacion > 100)
            return false;
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Ponderación máxima alcanzada';
    }
}
