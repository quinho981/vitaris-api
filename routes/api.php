<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTypesController;
use App\Http\Controllers\TranscriptController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::get('/tokens', [AuthController::class, 'tokens'])->middleware('auth:sanctum');
Route::delete('/tokens/{id}', [AuthController::class, 'revokeToken'])->middleware('auth:sanctum');

Route::post('generate-document', [DocumentController::class, 'generate'])->middleware('auth:sanctum');
Route::get('user/transcripts', [TranscriptController::class, 'indexByUser'])->middleware('auth:sanctum');
Route::get('user/transcripts/perDate', [TranscriptController::class, 'indexByUserPerDate'])->middleware('auth:sanctum');
Route::get('transcripts/{id}', [TranscriptController::class, 'show'])->middleware('auth:sanctum');
Route::delete('transcripts/{id}', [TranscriptController::class, 'delete'])->middleware('auth:sanctum');
Route::put('transcripts', [TranscriptController::class, 'update'])->middleware('auth:sanctum');

Route::get('templates', [DocumentTypesController::class, 'index'])->middleware('auth:sanctum');