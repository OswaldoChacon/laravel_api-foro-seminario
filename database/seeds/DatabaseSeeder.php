<?php

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
        $this->call(ForoSeeder::class);
        $this->call(RolSeeder::class);
        $this->call(TipoDeProyectoSeeder::class);
        $this->call(TipoDeSolicitudSeeder::class);
        $this->call(LineaInvestigacionSeeder::class);

        factory(App\User::class, 48)->create();
        factory(App\Proyecto::class, 5)->create();
        App\User::all()->each(function ($user) {
            $user->roles()->attach(App\Rol::find(2));
        });
        App\Proyecto::all()->each(function ($proyecto) {
            $proyecto->asesor()->associate(App\User::find(1));
        });
    }
}
