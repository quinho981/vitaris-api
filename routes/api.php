<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\TranscriptTypesController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');
Route::get('/tokens', [AuthController::class, 'tokens'])->middleware('auth:sanctum');
Route::delete('/tokens/{id}', [AuthController::class, 'revokeToken'])->middleware('auth:sanctum');

Route::post('generate-document', [DocumentController::class, 'generate'])->middleware('auth:sanctum');
Route::post('refine', [DocumentController::class, 'refine'])->middleware('auth:sanctum');
Route::post('document/{document}', [DocumentController::class, 'update'])->middleware('auth:sanctum');
Route::get('user/transcripts', [TranscriptController::class, 'indexByUser'])->middleware('auth:sanctum');
Route::get('transcripts/{id}', [TranscriptController::class, 'show'])->middleware('auth:sanctum');
Route::get('transcripts/{id}/conversations', [TranscriptController::class, 'getConversations'])->middleware('auth:sanctum');
Route::delete('transcripts/{id}', [TranscriptController::class, 'delete'])->middleware('auth:sanctum');
Route::post('transcripts', [TranscriptController::class, 'store'])->middleware('auth:sanctum');
Route::put('transcripts', [TranscriptController::class, 'update'])->middleware('auth:sanctum');
Route::get('transcripts/user/filter', [TranscriptController::class, 'filterUserTranscripts'])->middleware('auth:sanctum');

Route::get('templates', [DocumentTemplateController::class, 'index'])->middleware('auth:sanctum');
Route::get('templates/count', [DocumentTemplateController::class, 'userTemplatesWithDocumentsCount']);
Route::get('types', [TranscriptTypesController::class, 'index'])->middleware('auth:sanctum');

Route::get('/stream/insights-ai/{documentId}', function ($documentId) {
    return response()->stream(function () use ($documentId) {

        $timeout = 15;
        $start = time();
        while (true) {
            $response = Cache::pull("insights_ai_{$documentId}"); // pega e apaga

            if ($response) {
                echo "data: " . json_encode($response) . "\n\n";
                ob_flush();
                flush();
                break; // encerra a conexão após enviar
            }

            if ((time() - $start) > $timeout) {
                echo "event: timeout\n";
                echo "data: {}\n\n";
                ob_flush();
                flush();
                break;
            }

            sleep(1);
        }
    }, 200, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    ]);
});