<?php

use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $roles = [
            [
                'nombre_' => 'Administrador'
            ],
            [
                'nombre_' => 'Docente'
            ],
            [
                'nombre_' => 'Alumno'
            ]
        ];
        DB::table('roles')->insert($roles);
    }
}
