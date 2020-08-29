<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Notificaciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('emisor');
            $table->foreign('emisor')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedInteger('receptor');
            $table->boolean('administrador')->default(false);
            $table->foreign('receptor')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedInteger('proyecto_id');
            $table->foreign('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');


            $table->unsignedInteger('tipo_solicitud');
            $table->foreign('tipo_solicitud')->references('id')->on('tipos_solicitud')->onDelete('cascade');
            

            $table->unsignedInteger('anterior_asesor')->nullable();
            $table->foreign('anterior_asesor')->references('id')->on('users')->onDelete('cascade');            
            $table->unsignedInteger('nuevo_asesor')->nullable();
            $table->foreign('nuevo_asesor')->references('id')->on('users')->onDelete('cascade');

            $table->string('titulo_anterior')->nullable();
            $table->string('nuevo_titulo')->nullable();
            
            $table->string('motivo')->nullable();
            $table->string('comentarios')->nullable();
            $table->boolean('respuesta')->nullable();
            $table->date('fecha');
            // default(false);            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notificaciones');
    }
}
