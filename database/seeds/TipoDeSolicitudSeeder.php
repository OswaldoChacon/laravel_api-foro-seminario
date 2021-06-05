<?php

use Illuminate\Database\Seeder;

class TipoDeSolicitudSeeder extends Seeder
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
                'descripcion'=>'TIPO-1',
                'nombre' => 'REGISTRO DE PROYECTO'
            ],
            [
                'descripcion'=>'TIPO-2',
                'nombre' => 'CAMBIO DE ASESOR'
            ],
            [
                'descripcion'=>'TIPO-3',
                'nombre' => 'CAMBIO DE TITULO DEL PROYECTO'
            ],
            [
                'descripcion'=>'TIPO-4',
                'nombre' => 'CANCELACION DEL PROYECTO'
            ],
            [
                'descripcion'=>'TIPO-5',
                'nombre' => 'DAR DE BAJA A UN INTEGRANTE'
            ]
        ];
        DB::table('tipos_de_solicitud')->insert($tipos);
    }
}
