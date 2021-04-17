<?php

use Illuminate\Database\Seeder;

class TiposSolicitudSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tipos = [
            [
                // 'clave' => 'I',
                'nombre' => 'TESIS',
            ],
            [
                // 'clave' => 'DT',
                'nombre' => 'DESARROLLO TECNOLÓGICO (RESIDENCIA PROFESIONAL)',
            ],
            [
                // 'clave' => 'IT',
                'nombre' => 'INNOVACIÓN TECNOLÓGICA',
            ]
        ];
        DB::table('tipos_de_solicitud')->insert($tipos);
    }
}
