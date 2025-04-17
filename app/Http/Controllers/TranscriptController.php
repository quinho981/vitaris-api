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
        
        return $this->transcriptService->getTitleUserTranscripts($userId);
    }

    public function delete(int $id) {
        return $this->transcriptService->deleteTranscript($id);
    }
}
