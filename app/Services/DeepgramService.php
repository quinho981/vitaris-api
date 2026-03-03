<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DeepgramService
{
    public function transcribe($audioContent, $mimeType)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . config('services.deepgram.key'),
            'Content-Type' => $mimeType,
        ])
        ->withQueryParameters([
            'model' => 'nova-2',
            'language' => 'pt-BR',
            'punctuate' => 'true',
            'diarize' => 'true',
            'utterances' => 'true',
            'smart_format' => 'true',
            'multichannel' => 'false',
        ])
        ->withBody($audioContent, $mimeType)
        ->post('https://api.deepgram.com/v1/listen');

        return $response->json();
    }
}