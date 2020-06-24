<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HorarioJurado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horario_jurado', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('docente_id');
            $table->foreign('docente_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedInteger('fechas_foros_id');        
            $table->foreign('fechas_foros_id')->references('id')->on('fechas_foros')->onDelete('cascade');
            $table->string('hora');        
            $table->unsignedInteger('posicion');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('horario_jurado');
    }
}
