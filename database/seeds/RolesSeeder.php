<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Rol;

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
        User::findOrFail(1)->roles()->attach(Rol::findOrFail(1));
    }
}
