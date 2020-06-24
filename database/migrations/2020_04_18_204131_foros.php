<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Foros extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('foros', function (Blueprint $table) {            
            $table->increments('id');
            $table->string('slug')->unique();
            $table->integer('no_foro')->unique();
            $table->string('nombre');
            $table->string('periodo');
            $table->year('anio');
            $table->integer('lim_alumnos')->default(3);
            $table->integer('num_aulas')->default(3);
            $table->integer('num_maestros')->default(3);
            $table->integer('duracion')->default(30);
            $table->boolean('acceso')->default(false);
            $table->string('prefijo');            
            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('foros');
    }
}
