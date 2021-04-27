<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'activo' => true,
                'slug' => 'foro-1',
                'no_foro' => '1',
                'nombre' => 'FORO DE PROPUESTAS DE PROYECTOS PARA TITULACIÓN INTEGRAL',
                'periodo' => 'AGOSTO-DICIEMBRE',
                'anio' => 2021,
                'prefijo' => '2021_',
                'user_id' => 1
            ]
        ];
        DB::table('foros')->insert($roles);
    }
}
