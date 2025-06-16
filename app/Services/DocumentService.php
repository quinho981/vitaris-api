<?php

namespace App\Services;

use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use LucianoTonet\GroqLaravel\Facades\Groq;

class DocumentService
{
    protected const MODEL_NAME = 'llama-3.3-70b-versatile';

    protected TranscriptService $transcriptService;

    public function __construct(TranscriptService $transcriptService)
    {
        $this->transcriptService = $transcriptService;
    }

    public function storeDocument(int $transcriptId, array $data): Document
    {
        return Document::create($data, $transcriptId);
    }

    public function generateDocumentAndStore($request): JsonResponse
    {
        $documentContent = $this->generateLlmResponse($request['conversation']);
        $documentContent['title'] = $request['title'] ?? $documentContent['title'];

        DB::transaction(function () use ($request, $documentContent) {
            $transcript = $this->transcriptService->storeTranscript([
                'user_id' => Auth::id(),
                'title' => $documentContent['title'],
                'status' => $request['status'],
                'conversation' => $request['conversation']
            ]);

            $transcript->document()->create([
                'document_type_id' => 1,
                'title' => $documentContent['title'],
                'result' => $documentContent['content']
            ]);
        });
        
        return response()->json([
            'content' => $documentContent['content']
        ]);
    }

    public function generateLlmResponse(string $context): array
    {
        $promptTemplate = config('prompts.anamnesis');
        $prompt = str_replace('{context}', $context, $promptTemplate);
        
        $response = Groq::chat()->completions()->create([
            'model' => self::MODEL_NAME,
            'messages' => [
                [
                    'role' => 'user', 
                    'content' => $prompt
                ],
            ],
        ]);

        $title = $this->extractTitleFromContent($response['choices'][0]['message']['content']);
        
        return [
            'title' => $title,
            'content' => $response['choices'][0]['message']['content']
        ];
    }

    public function extractTitleFromContent($content) {
        preg_match("/<p class='text-xl font-bold mb-2'>(.*?)<\/p>/", $content, $matches);
        
        return $matches[1] ?? 'Consulta ' . Carbon::now()->format('Y/m/d H:i:s');
    }
}