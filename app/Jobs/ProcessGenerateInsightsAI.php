<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
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
            $medicalAnalysis = $insights['medical_analysis'];

            $response = $document->ai_insights()->create([
                'red_flags' => $medicalAnalysis['red_flags'],
                'case_severity' => $medicalAnalysis['case_severity'],
                'brief_description' => $medicalAnalysis['brief_description'],
                'possible_diagnoses' => $medicalAnalysis['possible_diagnoses'],
                'suggested_cid_codes' => $medicalAnalysis['suggested_cid_codes'],
                'suggested_exams' => $medicalAnalysis['suggested_exams'],
                'suggested_conducts' => $medicalAnalysis['suggested_conducts'],
                'missing_clinical_information' => $medicalAnalysis['missing_clinical_information']
            ]);

            Cache::put("insights_ai_{$this->documentId}", [
                'red_flags' => $response->red_flags,
                'case_severity' => $response->case_severity,
                'brief_description' => $response->brief_description,
                'possible_diagnoses' => $response->possible_diagnoses,
                'suggested_cid_codes' => $response->suggested_cid_codes,
                'suggested_exams' => $response->suggested_exams,
                'suggested_conducts' => $response->suggested_conducts,
                'missing_clinical_information' => $response->missing_clinical_information
            ], 60);

            $document->transcript()->update([
                'description' => $medicalAnalysis['brief_description'][0] ?? null
            ]);
        }
    }
}
