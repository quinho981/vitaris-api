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

    public function generateDocumentAndStore($request)
    {
        $documentContent = $this->generateLlmResponse($request['conversation']);
        $documentContent['patient'] = $request['patient'] ?? $documentContent['title'];

        $transcript = DB::transaction(function () use ($request, $documentContent) {
            $transcript = $this->transcriptService->storeTranscript([
                'user_id' => Auth::id(),
                'patient' => $documentContent['patient'],
                'conversation' => $request['conversation'],
                'template_id' => $request['template'],
                'type_id' => $request['type'],
                'end_conversation_time' => $request['endConversationTime']
            ]);

            $transcript->document()->create([
                'document_type_id' => 1,
                'patient' => $documentContent['patient'],
                'result' => $documentContent['content']
            ]);

            return $transcript;
        });
        
        return response()->json([
            'transcript_id' => $transcript->id,
            'content' => $documentContent['content']
        ]);
    }

    public function generateLlmResponse($context): array
    {
        $context = $this->mergeContextChunks($context);

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
        preg_match("/<h2><strong>(.*?)<\/strong><\/h2>/", $content, $matches);
        
        return $matches[1] ?? 'Consulta ' . Carbon::now()->format('Y/m/d H:i:s');
    }

    public function mergeContextChunks($contextChunks): string
    {
        $mergedContext = '';
        foreach ($contextChunks as $chunk) {
            $mergedContext .= $chunk['text'] . ' ';
        }
        return trim($mergedContext);    

    }
}