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
                // 'clave'=>'TIPO-1',
                'nombre_' => 'REGISTRO DE PROYECTO'
            ],
            [
                // 'clave'=>'TIPO-2',
                'nombre_' => 'CAMBIO DE ASESOR'
            ],
            [
                // 'clave'=>'TIPO-3',
                'nombre_' => 'CAMBIO DE TITULO DEL PROYECTO'
            ],
            [
                // 'clave'=>'TIPO-4',
                'nombre_' => 'CANCELACION DEL PROYECTO'
            ],
            [
                // 'clave'=>'TIPO-5',
                'nombre_' => 'DAR DE BAJA A UN INTEGRANTE'
            ]
        ];
        DB::table('tipos_de_solicitud')->insert($tipos);
    }
}
