<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Rol;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
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
                'nombre' => 'Administrador',
                'descripcion' => 'Administrador'
            ],
            [
                'nombre' => 'Docente',
                'descripcion' => 'Docente'
            ],
            [
                'nombre' => 'Alumno',
                'descripcion' => 'Alumno'
            ]
        ];
        DB::table('roles')->insert($roles);
        User::findOrFail(1)->roles()->attach(Rol::findOrFail(1));
    }
}
