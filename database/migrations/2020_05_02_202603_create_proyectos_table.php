<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProyectosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proyectos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('folio')->unique();
            $table->string('titulo')->unique();
            $table->string('empresa');
            $table->string('objetivo');
            $table->unsignedInteger('linea_de_investigacion_id');
            $table->foreign('linea_de_investigacion_id')->references('id')->on('lineas_de_investigacion')->onDelete('cascade');
            $table->unsignedInteger('tipo_deproyecto_id');
            $table->foreign('tipo_de_proyecto_id')->references('id')->on('tipos_de_proyecto')->onDelete('cascade');
            $table->unsignedInteger('foro_id');
            $table->foreign('foro_id')->references('id')->on('foros')->onDelete('cascade');
            $table->unsignedInteger('asesor_id')->nullable();
            $table->foreign('asesor_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('enviado')->default(false);            
            $table->boolean('aceptado')->default(false);
            $table->boolean('permitir_cambios')->default(false);
            $table->boolean('participa')->default(false);
            $table->boolean('cancelado')->default(false);
            $table->integer('calificacion_foro')->default(0);
            $table->integer('calificacion_seminario')->default(0);
            $table->integer('promedio')->default(0);
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('proyectos');
    }
}
