<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranscriptRequest;
use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Transcript;
use App\Services\DashboardService;
use App\Services\DeepgramService;
use App\Services\DocumentService;
use App\Services\TranscriptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TranscriptController extends Controller
{
    protected TranscriptService $transcriptService;
    protected DeepgramService $deepgramService;
    protected DashboardService $dashboardService;
    protected DocumentService $documentService;

    public function __construct(
        TranscriptService $transcriptService, 
        DeepgramService $deepgramService, 
        DashboardService $dashboardService,
        DocumentService $documentService
    )
    {
        $this->transcriptService = $transcriptService;
        $this->deepgramService = $deepgramService;
        $this->dashboardService = $dashboardService;
        $this->documentService = $documentService;
    }

    public function indexByUser() {
        $userId = Auth::id(); 
        
        return $this->transcriptService->getUserTranscripts($userId);
    }

    public function store(StoreTranscriptRequest $request): JsonResponse
    {
        $transcript = $this->transcriptService->processAudioAndCreate($request);

        return response()->json($transcript, 201);
    }

    public function storeAndGenerateDocument(StoreTranscriptRequest $request)
    {
        [
            'file' => $file,
            'utterances' => $utterances,
            'conversation' => $conversation
        ] = $this->transcriptService->processAudioAndBuildConversation($request);

        $documentContent = $this->documentService->generateLlmDocument($conversation, $request['template']);

        $document = DB::transaction(function () use ($request, $file, $utterances, $conversation, $documentContent) {
            $transcript = Transcript::create([
                'user_id' => Auth::id(),
                'patient' => $request['patient'],
                'conversation' => $conversation,
                'transcript_type_id' => $request['type'],
                'end_conversation_time' => $this->transcriptService->getLastEndUtteranceTime($utterances),
                'file_size' => $file->getSize()
            ]);
    
            $document = $transcript->document()->create([
                'document_template_id' => $request['template'],
                'patient' => $request['patient'],
                'result' => $documentContent,
                'transcript_id' => $request['transcript_id']
            ]);

            return $document;
        });

        ProcessGenerateInsightsAI::dispatch($document->id, $conversation);

        return $document;
    }

    public function show(Transcript $transcript)
    {
        $this->authorize('view', $transcript);

        return $this->transcriptService->getTranscriptAndDocument($transcript->id);
    }

    public function update(Transcript $transcript, Request $request): JsonResponse
    {
        $this->authorize('update', $transcript);

        $data = $request->all();
        
        $transcript->update($data);

        return response()->json([
            'success' => $transcript,
            'message' => 'Transcript updated successfully',
        ], 200);
    }

    public function delete(Transcript $transcript): JsonResponse
    {
        $this->authorize('delete', $transcript);

        $this->transcriptService->deleteTranscript($transcript->id);

        return response()->json([
            'success' => true,
            'message' => 'Transcript deleted successfully',
        ], 200);
    }

    public function getConversations(Transcript $transcript) 
    {
        $this->authorize('getConversations', $transcript);

        return $this->transcriptService->getConversations($transcript->id);
    }

    public function filterUserTranscripts(Request $request) 
    {
        $userId = Auth::id();

        return $this->transcriptService->searchUserTranscripts($request->all(), $userId);
    }

    public function getDashboardSummary() 
    {
        return $this->dashboardService->summary();
    }
    
    public function getDashboardCharts() 
    {
        return $this->dashboardService->charts();
    }

    public function getlatestRecentTranscripts()
    {
        return $this->dashboardService->latestRecentTranscripts();
    }
}
