<?php

use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(TiposProyectosSeeder::class);
        $this->call(TiposSolicitudSeeder::class);
        $this->call(LineaInvestigacionSeeder::class);

        factory(User::class, 48)->create();
        App\User::all()->each(function ($user) {
            $user->roles()->attach(
                App\Rol::find(2)
            );
        });
    }
}
