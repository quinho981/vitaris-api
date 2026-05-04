<?php

use App\Enums\PriceIdsEnum;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\TranscriptTypesController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::delete('/tokens/{id}', [AuthController::class, 'revokeToken']);

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::put('/', [UserController::class, 'update']);
    });
    
    Route::prefix('documents')->group(function () {
        Route::post('/generate', [DocumentController::class, 'generate']);
        Route::post('/refine', [DocumentController::class, 'refine'])->middleware('subscription');
        Route::put('/{document}', [DocumentController::class, 'update']);
        Route::get('/{document}/pdf', [DocumentController::class, 'generatePdf']);
    });
    Route::get('user/transcripts', [TranscriptController::class, 'indexByUser']);

    Route::prefix('transcripts')->group(function () {
        Route::post('/', [TranscriptController::class, 'store']);
        Route::post('/generate-document', [TranscriptController::class, 'storeAndGenerateDocument']);
        Route::get('/user/filter', [TranscriptController::class, 'filterUserTranscripts']);
        Route::put('/{transcript}', [TranscriptController::class, 'update']);
        Route::get('/{transcript}', [TranscriptController::class, 'show']);
        Route::get('/{transcript}/conversations', [TranscriptController::class, 'getConversations']);
        Route::delete('/{transcript}', [TranscriptController::class, 'delete']);
    });

    Route::prefix('templates')->group(function () {
        Route::get('/', [DocumentTemplateController::class, 'index']);
        Route::get('/minimal', [DocumentTemplateController::class, 'listIdNameTemplate']);
        Route::get('/with-documents-count', [DocumentTemplateController::class, 'listTemplatesWithUserDocumentsCount']);
        Route::get('/count-categories', [DocumentTemplateController::class, 'listCountCategories']);
    });
    Route::get('transcript-types', [TranscriptTypesController::class, 'index']);
    Route::get('transcript-types/minimal', [TranscriptTypesController::class, 'listMinimal']);
    
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [TranscriptController::class, 'getDashboardSummary']);
        Route::get('/charts', [TranscriptController::class, 'getDashboardCharts']);
        Route::get('/last-transcripts', [TranscriptController::class, 'getlatestRecentTranscripts']);
    });

    Route::prefix('subscription')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index']);
        Route::post('/checkout', [SubscriptionController::class, 'checkout']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
    });
});

Route::get('/stream/insights-ai/{documentId}', function ($documentId) {
    return response()->stream(function () use ($documentId) {

        $timeout = 15;
        $start = time();
        while (true) {
            $response = Cache::pull("insights_ai_{$documentId}"); // pega e apaga

            if ($response) {
                ob_flush();
                flush();
                break; // encerra a conexão após enviar
            }

            if ((time() - $start) > $timeout) {
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