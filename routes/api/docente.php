<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['jwtAuth:Docente']], function () {
    Route::put('permitir_cambios/{proyecto}', 'ProyectoController@permitirCambios');
});