<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transcript;
use App\Services\DashboardService;
use App\Services\DeepgramService;
use App\Services\TranscriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranscriptController extends Controller
{
    protected TranscriptService $transcriptService;
    protected DeepgramService $deepgramService;
    protected DashboardService $dashboardService;

    public function __construct(
        TranscriptService $transcriptService, 
        DeepgramService $deepgramService, 
        DashboardService $dashboardService
    )
    {
        $this->transcriptService = $transcriptService;
        $this->deepgramService = $deepgramService;
        $this->dashboardService = $dashboardService;
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
        $mimeType = $file->getMimeType();
        $audioContent = file_get_contents($file->getRealPath());

        $result = $this->deepgramService->transcribe($audioContent, $mimeType);

        return response()->json($result);
    }

    public function show(int $id) {
        return $this->transcriptService->getTranscriptAndDocument($id);
    }

    public function update(Transcript $transcript, Request $request) {
        $data = $request->all();
        
        $transcript->update($data);

        return response()->json([
            'success' => $transcript,
            'message' => 'Transcript updated successfully',
        ], 200);
    }

    public function delete(int $id) {
        return $this->transcriptService->deleteTranscript($id);
    }

    public function getConversations(int $id) {
        return $this->transcriptService->getConversations($id);
    }

    public function filterUserTranscripts(Request $request) {
        $request = $request->all();
        return $this->transcriptService->searchUserTranscripts($request);
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
