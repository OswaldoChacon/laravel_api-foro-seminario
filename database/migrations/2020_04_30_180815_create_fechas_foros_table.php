<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFechasForosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fechas_foros', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('foro_id');
            $table->foreign('foro_id')->references('id')->on('foros')->onDelete('cascade');
            $table->date('fecha')->unique();
            $table->time('hora_inicio');
            $table->time('hora_termino');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fechas_foros');
    }
}
