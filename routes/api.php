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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::delete('/tokens/{id}', [AuthController::class, 'revokeToken']);

    Route::prefix('documents')->group(function () {
        Route::post('/generate', [DocumentController::class, 'generate']);
        Route::post('/refine', [DocumentController::class, 'refine']);
        Route::put('/{document}', [DocumentController::class, 'update']);
    });
    Route::get('user/transcripts', [TranscriptController::class, 'indexByUser']);

    Route::prefix('transcripts')->group(function () {
        Route::post('/', [TranscriptController::class, 'store']);
        Route::get('/user/filter', [TranscriptController::class, 'filterUserTranscripts']);
        Route::put('/{transcript}', [TranscriptController::class, 'update']);
        Route::get('/{id}', [TranscriptController::class, 'show']);
        Route::get('/{id}/conversations', [TranscriptController::class, 'getConversations']);
        Route::delete('/{id}', [TranscriptController::class, 'delete']);
    });

    Route::prefix('templates')->group(function () {
        Route::get('/', [DocumentTemplateController::class, 'index']);
        Route::get('/with-documents-count', [DocumentTemplateController::class, 'userTemplatesWithDocumentsCount']);
    });
    Route::get('transcript-types', [TranscriptTypesController::class, 'index']);
    
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [TranscriptController::class, 'getDashboardSummary']);
        Route::get('/charts', [TranscriptController::class, 'getDashboardCharts']);
        Route::get('/last-transcripts', [TranscriptController::class, 'getlatestRecentTranscripts']);
    });
});


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