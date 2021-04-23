<?php

namespace App\Rules;

use App\Concepto;
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
    public function __construct($plantillaGrupo, $grupoConcepto, int $ponderacion)
    {
        // $this->grupo = $grupo;
        $this->plantillaGrupo = $plantillaGrupo;
        $this->grupoConcepto = $grupoConcepto;
        $this->ponderacion = $ponderacion;
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
        if ($this->plantillaGrupo instanceof Plantilla)
            $this->ponderacion += $this->plantillaGrupo->grupos->sum('ponderacion');
        else if ($this->plantillaGrupo instanceof Grupo)
            $this->ponderacion += $this->plantillaGrupo->conceptos->sum('ponderacion');
        if ($this->grupoConcepto)
            $this->ponderacion -= $this->grupoConcepto->ponderacion;
        return $this->ponderacion > 100 ? false : true;
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
