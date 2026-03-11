<?php

namespace App\Services;

use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $documentContent = $this->generateLlmDocument($request['conversation']);
        $documentContent['patient'] = $request['patient'] ?? $documentContent['title'];

        $transcript = DB::transaction(function () use ($request, $documentContent) {
            $transcript = $this->transcriptService->storeTranscript([
                'user_id' => Auth::id(),
                'patient' => $documentContent['patient'],
                'conversation' => $request['conversation'],
                'transcript_type_id' => $request['type'],
                'end_conversation_time' => $request['endConversationTime'],
                'file_size' => $request['fileSize'] ?? null
            ]);

            $document = $transcript->document()->create([
                'document_template_id' => $request['template'],
                'patient' => $documentContent['patient'],
                'result' => $documentContent['content']
            ]);

            ProcessGenerateInsightsAI::dispatch($document->id, $request['conversation']);

            return $transcript;
        });
        
        return response()->json([
            'transcript_id' => $transcript->id,
            'content' => $documentContent['content']
        ]);
    }

    public function generateLlmDocument($context): array
    {
        $response = $this->llmResponseByTemplate($context, 'anamnesis');
        $title = $this->extractTitleFromContent($response);
        
        return [
            'title' => $title,
            'content' => $response
        ];
    }

    public function llmResponseByTemplate($context, $template, bool $forceJsonFormat = false): string
    {
        $context = $this->mergeContextChunks($context);

        $promptTemplate = config("prompts.$template");
        $prompt = str_replace('{context}', $context, $promptTemplate);

        $payload = [
            'model' => self::MODEL_NAME,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
        ];

        if ($forceJsonFormat) {
            $payload['response_format'] = [ 'type' => 'json_object' ];
        }

        try {
            $response = Groq::chat()->completions()->create($payload);
        } catch (\Throwable $e) {
            Log::error('Erro no Groq: ' . $e->getMessage());
            throw $e;
        }

        return $response['choices'][0]['message']['content'];
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

    public function generateInsightsAI($context) {
        $insights = $this->llmResponseByTemplate($context, 'ai_insights', true);
        return json_decode($insights, true);
    }

    public function refineDocument(array $data): string
    {
        $instructions = $this->buildRefinementInstructions(
            $data['refinements'] ?? [],
            $data['custom_instruction'] ?? null
        );

        $promptTemplate = config("prompts.anamnesis_dynamic_refine");

        $prompt = str_replace(
            ['{instructions}', '{context}'],
            [$instructions, $data['conversation']],
            $promptTemplate
        );

        $response = Groq::chat()->completions()->create([
            'model' => self::MODEL_NAME,
            'temperature' => 0.2,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ],
        ]);

        return $response['choices'][0]['message']['content'];
    }

    private function buildRefinementInstructions(array $refinements, ?string $custom): string
    {
        $instructions = [];

        if (in_array('clarity', $refinements)) {
            $instructions[] = "- Improve clarity and sentence structure for better readability.";
        }

        if (in_array('technical', $refinements)) {
            $instructions[] = "- Use more formal and technical medical terminology.";
        }

        if (in_array('soap', $refinements)) {
            $instructions[] = "- Reorganize the document into SOAP format (Subjetivo, Objetivo, Avaliação, Plano).";
        }

        if (!empty($custom)) {
            $instructions[] = "- Additional instruction: " . $custom;
        }

        if (empty($instructions)) {
            $instructions[] = "- Improve the overall quality while maintaining structure.";
        }

        return implode("\n", $instructions);
    }
}