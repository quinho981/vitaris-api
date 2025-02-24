<?php

namespace App\Services;

use App\Models\Transcript;

class TranscriptService
{
    public function storeTranscript(array $data): Transcript
    {
        return Transcript::create($data);
    }
}