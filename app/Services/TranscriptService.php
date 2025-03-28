<?php

namespace App\Services;

use App\Models\Transcript;

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
            ->get();
    }
}