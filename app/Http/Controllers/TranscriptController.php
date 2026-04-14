<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transcript;
use App\Services\DashboardService;
use App\Services\DeepgramService;
use App\Services\DocumentService;
use App\Services\TranscriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function store(Request $request)
    {
        $request->validate([
            'audio' => 'required|file'
        ]);
        $file = $request->file('audio');
        
        $audio = $this->getAudioContent($file);

        $result = $this->deepgramService->transcribe($audio['content'], $audio['mimeType']);

        return response()->json($result);
    }

    public function storeAndGenerateDocument(Request $request)
    {
        $request->validate([
            'type' => 'required|integer',
            'template' => 'required|integer',
            'patient' => 'nullable|string',
            'audio' => 'required|file'
        ]);
        $file = $request->file('audio');

        $audio = $this->getAudioContent($file);

        $resultTranscribe = $this->deepgramService->transcribe($audio['content'], $audio['mimeType']);
        $utterances = $resultTranscribe['results']['utterances'];
        
        $conversation = $this->organizeUtterances($utterances);

        $result = $this->documentService->generateDocumentAndStore(
            array_merge($request->all(), [
                'conversation' => $conversation,
                'endConversationTime' => $this->getLastEndUtteranceTime($utterances),
                'fileSize' => $file->getSize()
            ]));

        return response()->json($result->original);
    }

    private function getAudioContent($file)
    {
        $mimeType = $file->getMimeType();
        $content = file_get_contents($file->getRealPath());

        return ['content' => $content, 'mimeType' => $mimeType];
    }
    
    private function organizeUtterances($utterances)
    {
        $conversation = [];

        foreach ($utterances as $utterance) {
            $conversation[] = [
                // 'speaker' => $utterance['speaker'],
                'text' => $utterance['transcript'],
                'start' => floor($utterance['start']),
                'end' => floor($utterance['end'])
            ];
        }

        return $conversation;
    }

    private function getLastEndUtteranceTime($utterances)
    {
        if (empty($utterances)) {
            return 0;
        }

        $lastUtterance = end($utterances);
        return floor($lastUtterance['end']);
    }

    public function show(Transcript $transcript) {
        $this->authorize('view', $transcript);

        return $this->transcriptService->getTranscriptAndDocument($transcript->id);
    }

    public function update(Transcript $transcript, Request $request) {
        $this->authorize('update', $transcript);

        $data = $request->all();
        
        $transcript->update($data);

        return response()->json([
            'success' => $transcript,
            'message' => 'Transcript updated successfully',
        ], 200);
    }

    public function delete(Transcript $transcript)
    {
        $this->authorize('delete', $transcript);

        return $this->transcriptService->deleteTranscript($transcript->id);
    }

    public function getConversations(Transcript $transcript) {
        $this->authorize('getConversations', $transcript);

        return $this->transcriptService->getConversations($transcript->id);
    }

    public function filterUserTranscripts(Request $request) {
        $userId = Auth::id();
        $request = $request->all();

        return $this->transcriptService->searchUserTranscripts($request, $userId);
    }

    public function getDashboardSummary() {
        return $this->dashboardService->summary();
    }
    
    public function getDashboardCharts() {
        return $this->dashboardService->charts();
    }

    public function getlatestRecentTranscripts()
    {
        return $this->dashboardService->latestRecentTranscripts();
    }
}
