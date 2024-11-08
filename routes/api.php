<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SearchCityController;
use App\Http\Controllers\UserController;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/


Route::get('search-cities',[SearchCityController::class, 'searchCities']);
Route::post('users/register', [UserController::class, 'register']);
Route::post('users/login', [UserController::class, 'login']);
Route::post('users/logout', [UserController::class, 'logout'])->middleware('auth:api');
