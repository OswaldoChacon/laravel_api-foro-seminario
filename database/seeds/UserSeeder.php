<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $empleados = [
            [
                'nombre'=>'admin',
                'apellidoP'=>'admin',
                'apellidoM'=>'admin',
                'prefijo'=>'admin',
                'email'=>'admin@admin.com',
                'password'=>'$2y$10$EASJqhjcNpFHQ3wOnuuLd.YeeN8Bu9X9Yp5N3Id2/GEjSaKmAzgPi',
                'num_control'=>'15270717',
                // 'created_at' => Carbon::now(),
                // 'updated_at' => Carbon::now(),
            ]
        ];
        DB::table('users')->insert($empleados);
    }
}
