<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Lineasdeinvestigacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lineasdeinvestigacion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clave')->unique();
            $table->string('nombre')->unique();            
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
        Schema::dropIfExists('lineasdeinvestigacion');
    }
}
