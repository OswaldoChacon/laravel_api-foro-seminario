<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificacionesTable extends Migration
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
            $table->string('folio');
            $table->unsignedInteger('emisor_id');
            $table->foreign('emisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedInteger('receptor_id');
            $table->foreign('receptor_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('administrador')->default(false);
            $table->unsignedInteger('proyecto_id');
            $table->foreign('proyecto_id')->references('id')->on('proyectos')->onDelete('cascade');


            $table->unsignedInteger('tipo_de_solicitud_id');
            $table->foreign('tipo_de_solicitud_id')->references('id')->on('tipos_de_solicitud')->onDelete('cascade');
            

            $table->unsignedInteger('anterior_asesor_id')->nullable();
            $table->foreign('anterior_asesor_id')->references('id')->on('users')->onDelete('cascade');            
            $table->unsignedInteger('nuevo_asesor_id')->nullable();
            $table->foreign('nuevo_asesor_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('titulo_anterior')->nullable();
            $table->string('titulo_nuevo')->nullable();
            
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
