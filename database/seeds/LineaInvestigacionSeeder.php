<?php

use Illuminate\Database\Seeder;

class LineaInvestigacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lineas = [
            [
                'clave' => 'LGAC-2017-TGTZ-ISCO-29',
                'nombre' => 'TECNOLOGÍAS DE DESARROLLO WEB Y MÓVIL'
            ],
            [
                'clave' => 'LGAC-2017-TGTZ-ISCO-13',
                'nombre' => 'DESARROLLO DE SOFTWARE E INFRAESTRUCTURA DE RED'
            ],
            [
                'clave' => 'LGAC-2017-TGTZ-ISCO-14',
                'nombre' => 'ROBÓTICA, CONTROL INTELIGENTE Y SISTEMAS DE PERCEPCIÓN'
            ],
        ];
        DB::table('lineas_de_investigacion')->insert($lineas);
    }
}
