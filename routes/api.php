<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


//this is the endpoint with prefix /api
Route::post('/login', [UsersController::class, 'login']);
Route::post('/register', [UsersController::class, 'register']);
Route::post('/book', [AppointmentsController::class, 'store']);
Route::get('/appointments', [AppointmentsController::class, 'index']);


//modify this     
//this group mean return user's data if authenticated successfully
Route::middleware('auth:sanctum')->group(function() {
    Route::get('/user', [UsersController::class, 'index']);
    Route::post('/book', [AppointmentsController::class, 'store']);
    Route::get('/appointments', [AppointmentsController::class, 'index']);
    Route::post('/reviews', [DocsController::class, 'store']);
    Route::post('/doctordash', [DocsController::class, 'index']);
    Route::post('/fav', [UsersController::class, 'storeFavDoc']);
    Route::post('/logout', [UsersController::class, 'logout']);
    });