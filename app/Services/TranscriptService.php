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

    public function __construct(Transcript $transcript)
    {
        $this->transcript = $transcript;
    }

    public function storeTranscript(array $data): Transcript
    {
        return $this->transcript->create($data);
    }

    public function getUserTranscripts(int $userId): LengthAwarePaginator
    {
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
                'document.documentTemplate:id,name',
                'transcriptType:id,type'
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