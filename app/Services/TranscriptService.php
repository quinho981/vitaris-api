<?php

namespace App\Services;

use App\Models\Transcript;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class TranscriptService
{
    protected Transcript $transcript;

    public function __construct(Transcript $transcript)
    {
        $this->transcript = $transcript;
    }

    public function storeTranscript(array $data): Transcript
    {
        return $this->transcript->create($data);
    }

    public function updateTranscript(int $id, array $data): JsonResponse|bool
    {
        $transcript = $this->transcript->find($id);

        if (!$transcript) {
            return false;
        }

        return response()->json([
            'success' => $transcript->update($data),
            'message' => 'Transcript updated successfully',
        ], 200);
    }

    public function getTitleUserTranscripts(int $userId): object
    {
        return $this->transcript
            ->with([
                'document:id,transcript_id,document_template_id',
                'document.documentTemplate:id,name',
                'transcriptType:id,type'
            ])
            ->where('user_id', $userId)
            ->select('id', 'transcript_type_id', 'patient', 'end_conversation_time', 'created_at')
            ->latest()
            ->paginate(10);
    }

    public function getTranscriptAndDocument(int $id): object
    {
        return $this->transcript
            ->with([
                'document:id,transcript_id,document_template_id,result,created_at',
                'document.documentTemplate:id,name',
                'document.ai_insights:id,document_id,main_topics,identified_symptoms,possible_diagnoses'
            ])
            ->where('id', $id)
            ->firstOrFail(['id', 'patient', 'created_at', 'end_conversation_time']);
    }

    public function getTitleUserTranscriptsPerDate(int $userId): object
    {
        $paginator = $this->transcript
            ->where('user_id', $userId)
            ->select('id', 'patient', 'created_at')
            ->latest()
            ->paginate(15);

        $bucketed = $paginator
            ->getCollection()
            ->groupBy(function($t) {
                $dt = $t->created_at;
                if ($dt->isToday()) {
                    return 'Hoje';
                }
                if ($dt->isYesterday()) {
                    return 'Ontem';
                }
                if ($dt->greaterThan(now()->subDays(7))) {
                    return 'Últimos 7 dias';
                }
                return 'Mais de 30 dias';
            });

        $grouped = $bucketed->map(function($items, $label) {
            return [
                'label' => $label,
                'items' => $items->map(fn($t) => [
                    'label'      => $t->title,
                    'icon'       => 'pi pi-fw pi-home',
                    'to'         => "/transcripts/{$t->id}",
                    'created_at' => $t->created_at->format('d/m/Y H:i:s'),
                ])->values()->all(),
            ];
        })->values();

        $paginator->setCollection($grouped);
        return $paginator;
    }

    public function deleteTranscript(int $id): JsonResponse|bool
    {
        $transcript = $this->transcript->find($id);

        if (!$transcript) {
            return false;
        }

        return response()->json([
            'success' => $transcript->delete(),
            'message' => 'Transcript deleted successfully',
        ], 200);
    }

    public function getConversations(int $id): object
    {
        $transcript = $this->transcript
            ->where('id', $id)
            ->first(['id', 'conversation']);

        return $transcript;
    }
}