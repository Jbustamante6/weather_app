<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SearchCityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FavoritesController;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/


Route::get('search-cities',[SearchCityController::class, 'searchCities']);
Route::get('/ciudades', [SearchCityController::class, 'listarCiudades']);
Route::post('/users/register', [UserController::class, 'register']);
Route::post('/users/login', [UserController::class, 'login']);
Route::post('/users/logout', [UserController::class, 'logout'])->middleware('auth:api');
Route::post('/ciudades/favoritas', [FavoritesController::class, 'agregarFavorito'])->middleware('auth:api');
Route::get('/ciudades/favoritas', [FavoritesController::class, 'listarFavoritos'])->middleware('auth:api');
Route::delete('/ciudades/favoritas/{id}', [FavoritesController::class, 'eliminarFavorito'])->middleware('auth:api');
Route::get('/ciudades', [SearchCityController::class, 'listarCiudades']);



