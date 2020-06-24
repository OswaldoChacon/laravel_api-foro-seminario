<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Horariobreak extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('horariobreak', function (Blueprint $table) {
            $table->increments('id');
            $table->string('hora');
            $table->integer('posicion')->unique();    
            //$table->integer('disponible');
            $table->unsignedInteger('fechas_foros_id');
            //$table->foreign('fechas_foros_id')->references('id')->on('fechas_foros')->onDelete('cascade');
            $table->foreign('fechas_foros_id')->references('id')->on('fechas_foros')->onDelete('cascade');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('horariobreak');
    }
}
