<?php

namespace App\Services;

use App\Models\Transcript;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranscriptService
{
    protected Transcript $transcript;
    protected DeepgramService $deepgramService;

    public function __construct(
        Transcript $transcript, 
        DeepgramService $deepgramService
    )
    {
        $this->transcript = $transcript;
        $this->deepgramService = $deepgramService;
    }

    public function getUserTranscripts(int $userId): LengthAwarePaginator
    {
        // TODO: aplicar cache com redis
        return $this->baseTranscriptHistoryQuery()
            ->where('user_id', $userId)
            ->paginate(10);
    }

    public function searchUserTranscripts($request, $userId): Collection
    {
        $username = $request['user'] ?? null;
        $date = $request['date'] ?? null;
        $type = $request['type'] ?? null;

        $query = $this->baseTranscriptHistoryQuery()
            ->where('user_id', $userId);

        if($username) {
            $query->where('patient', 'ILIKE', "%{$username}%");
        }

        if($date) {
            $date = Carbon::parse($request['date'])->toDateString();

            $query->whereDate('created_at', $date);
        }

        if ($type) {
            $query->where('transcript_type_id', $type);
        }
         
        return $query->limit(30)->get();
    }

    private function baseTranscriptHistoryQuery()
    {
        return $this->transcript
            ->with([
                'document:id,transcript_id,document_template_id',
                'document.documentTemplate:id,name,category_id',
                'document.documentTemplate.category:id,color',
                'transcriptType:id,type',
            ])
            ->select(['id', 'transcript_type_id', 'patient', 'end_conversation_time', 'file_size', 'description', 'created_at'])
            ->selectRaw('LEFT(description, 86) as description')
            ->latest();
    }

    public function getTranscriptAndDocument(int $id): object
    {
        return $this->transcript
            ->with([
                'document:id,transcript_id,document_template_id,result,created_at',
                'document.documentTemplate:id,name',
                'document.ai_insights:id,document_id,possible_diagnoses,red_flags,case_severity,brief_description,possible_diagnoses,suggested_cid_codes,suggested_exams,suggested_conducts,missing_clinical_information'
            ])
            ->where('id', $id)
            ->firstOrFail(['id', 'patient', 'created_at', 'end_conversation_time']);
    }

    public function deleteTranscript(int $id): void
    {
        $transcript = $this->transcript->findOrFail($id);
        $transcript->delete();
    }

    public function getConversations(int $id): object
    {
        $transcript = $this->transcript
            ->where('id', $id)
            ->first(['id', 'conversation']);

        return $transcript;
    }

    public function processAudioAndBuildConversation($request): array
    {
        $file = $request->file('audio');
  
        $audio = $this->getAudioContent($file);
        $utterances = $this->deepgramService->transcribeAudio($audio['content'], $audio['mimeType']);
        $conversation = $this->organizeUtterances($utterances);

        return [
            'file' => $file,
            'utterances' => $utterances,
            'conversation' => $conversation,
        ];
    }

    public function processAudioAndCreate($request): Transcript
    {
        [
            'file' => $file,
            'utterances' => $utterances,
            'conversation' => $conversation
        ] = $this->processAudioAndBuildConversation($request);

        $transcript = Transcript::create([
            'user_id' => Auth::id(),
            'patient' => $request['patient'],
            'conversation' => $conversation,
            'transcript_type_id' => $request['type'],
            'end_conversation_time' => $this->getLastEndUtteranceTime($utterances),
            'file_size' => $file->getSize()
        ]);

        return $transcript;
    }

    private function getAudioContent($file): array
    {
        $mimeType = $file->getMimeType();
        $content = file_get_contents($file->getRealPath());

        return ['content' => $content, 'mimeType' => $mimeType];
    }

    private function organizeUtterances($utterances): array
    {
        $conversation = [];

        foreach ($utterances as $utterance) {
            $conversation[] = [
                // 'speaker' => $utterance['speaker'],
                'text' => $utterance['transcript'],
                'start' => round($utterance['start'], 2),
                'end' => round($utterance['end'], 2)
            ];
        }

        return $conversation;
    }

    public function getLastEndUtteranceTime($utterances)
    {
        if (empty($utterances)) return 0;

        $lastUtterance = end($utterances);
        // TODO: AJUSTAR TIPO DE DADO NO BANCO PARA CONSEGUIR REGISTRAR FLOAT
        return floor($lastUtterance['end']);
    }
}