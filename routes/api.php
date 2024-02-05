<?php

use App\Http\Controllers\AirportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Middleware\ApiAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Route::get("/airports", [AirportController::class, "index"]);
// Route::post("/airport", [AirportController::class, "create"]);


Route::post("/registration", [AuthController::class, "signup"]);
Route::post("/authorization", [AuthController::class, "login"]);
Route::group(['middleware' => 'ApiAuth'], function () {
    Route::post("/files", [FileController::class, "upload"]);
    Route::get("/files/{file_id}", [FileController::class, "getFile"]);
});
