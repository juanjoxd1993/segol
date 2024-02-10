<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Backend\GuidesRegisterController; 
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/operaciones/registro-movimiento-existencias/guardar', [GuidesRegisterController::class, 'apistore']);
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
