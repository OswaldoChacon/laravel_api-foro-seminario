<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Foro;
use App\LineaDeInvestigacion;
use App\Proyecto;
use App\TipoDeProyecto;
use App\User;
use Faker\Generator as Faker;

$factory->define(Proyecto::class, function (Faker $faker) {
    return [
        //
        'folio' => $faker->uuid,
        'titulo' => $faker->company,
        'empresa' => $faker->company,
        'objetivo' => $faker->text($maxNbChars = 200),
        'linea_de_investigacion_id' => LineaDeInvestigacion::select('id')->orderByRaw("RAND()")->first()->id,
        'tipo_de_proyecto_id' => TipoDeProyecto::select('id')->orderByRaw("RAND()")->first()->id,
        'aceptado' => 1,
        'foro_id' => 1,
        'asesor_id' => 1,//User::select('id')->orderByRaw("RAND()")->first()->id,
    ];
});
