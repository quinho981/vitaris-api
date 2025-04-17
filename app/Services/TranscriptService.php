<?php

namespace App\Services;

use App\Models\Transcript;
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

    public function getTitleUserTranscripts(int $userId): object
    {
        return $this->transcript
            ->where('user_id', $userId)
            ->select('id', 'title', 'created_at')
            ->latest()
            ->paginate(10);
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