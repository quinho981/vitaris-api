<?php

namespace App\Services;

use App\Jobs\ProcessGenerateInsightsAI;
use App\Models\Document;
use App\Models\DocumentTemplate;
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

    public function createDocumentAndDispatchInsights($request)
    {
        $documentContent = $this->generateLlmDocument($request['conversation'], $request['template']);

        $document = Document::create([
            'document_template_id' => $request['template'],
            'patient' => $request['patient'],
            'result' => $documentContent,
            'transcript_id' => $request['transcript_id']
        ]);

        ProcessGenerateInsightsAI::dispatch($document->id, $request['conversation']);

        return $document;
    }

    public function generateLlmDocument($context, $templateId)
    {   
        $template = DocumentTemplate::findOrFail($templateId);

        $response = $this->llmResponseByTemplate($context, $template->content);
        
        return $response;
    }

    public function llmResponseByTemplate($context, $template, bool $forceJsonFormat = false): string
    {
        $context = $this->mergeContextChunks($context);

        $prompt = str_replace('{context}', $context, $template);

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

    public function mergeContextChunks($contextChunks): string
    {
        $mergedContext = '';
        foreach ($contextChunks as $chunk) {
            $mergedContext .= $chunk['text'] . ' ';
        }
        return trim($mergedContext);    

    }

    public function generateInsightsAI($context) 
    {
        $promptTemplate = config("prompts.ai_insights");
        $insights = $this->llmResponseByTemplate($context, $promptTemplate, true);
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