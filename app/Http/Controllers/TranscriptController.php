<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TranscriptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranscriptController extends Controller
{
    protected TranscriptService $transcriptService;

    public function __construct(TranscriptService $transcriptService)
    {
        $this->transcriptService = $transcriptService;
    }

    public function indexByUser() {
        $userId = Auth::id(); 
        
        return $this->transcriptService->getUserTranscripts($userId);
    }
    
    // public function indexByUserPerDate() {
    //     $userId = Auth::id();
        
    //     return $this->transcriptService->getTitleUserTranscriptsPerDate($userId);
    // }

    public function show(int $id) {
        return $this->transcriptService->getTranscriptAndDocument($id);
    }

    public function update() {
        $request = request()->all();
        return $this->transcriptService->updateTranscript($request['id'], $request);
    }

    public function delete(int $id) {
        return $this->transcriptService->deleteTranscript($id);
    }

    public function getConversations(int $id) {
        return $this->transcriptService->getConversations($id);
    }
}
