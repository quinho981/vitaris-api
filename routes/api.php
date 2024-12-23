<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BotController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::get('/tokens', [AuthController::class, 'tokens'])->middleware('auth:sanctum');
Route::delete('/tokens/{id}', [AuthController::class, 'revokeToken'])->middleware('auth:sanctum');

Route::post('generate-anamnese', [BotController::class, 'generateAnamnese']);