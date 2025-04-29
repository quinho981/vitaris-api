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
            ->where('user_id', $userId)
            ->select('id', 'title', 'created_at')
            ->latest()
            ->paginate(10);
    }

    public function getTitleUserTranscriptsPerDate(int $userId): object
    {
        $paginator = $this->transcript
            ->where('user_id', $userId)
            ->select('id','title','created_at')
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
}