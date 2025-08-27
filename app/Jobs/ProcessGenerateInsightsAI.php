<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessGenerateInsightsAI implements ShouldQueue
{
    use Queueable;

    protected $documentId;
    protected $conversation;

    /**
     * Create a new job instance.
     */
    public function __construct(int $documentId, array $conversation)
    {
        $this->documentId = $documentId;
        $this->conversation = $conversation;
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentService $documentService)
    {
        $document = Document::find($this->documentId);

        if($document) {
            $insights = $documentService->generateInsightsAI($this->conversation);
            // Log::info('Storing AI Insights for Document ID ' . $this->documentId . ': ' . $insights);

            $document->ai_insights()->create([
                'main_topics' => $insights['medical_analysis']['main_topics'] ?? [],
                'identified_symptoms' => $insights['medical_analysis']['identified_symptoms'] ?? [],
                'possible_diagnoses' => $insights['medical_analysis']['possible_diagnoses'] ?? [],
            ]);
        }
    }
}
