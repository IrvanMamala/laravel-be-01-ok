<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);
Route::get('/dashboard',[UserController::class,'dashboard']);
Route::get('/logout',[UserController::class,'logout']);
Route::get('/users', [UserController::class, 'allUsers']);

