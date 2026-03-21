<?php

namespace App\Services;

use App\Models\Transcript;
use App\Models\TranscriptType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardService
{
    public function summary() 
    {
        $userId = Auth::id();
        $startToday = now()->startOfDay();
        $endToday = now()->endOfDay();
        
        $totalTranscripts = $this->baseQuery($userId, $startToday, $endToday)->count();
        $totalTimeTranscripts = $this->baseQuery($userId, $startToday, $endToday)->sum('end_conversation_time');

        // CRIAR UM ENUM PARA OS TRANSCRIPTS TYPES
        // CRIAR UM ENUM PARA OS TRANSCRIPTS TYPES
        // CRIAR UM ENUM PARA OS TRANSCRIPTS TYPES
        // CRIAR UM ENUM PARA OS TRANSCRIPTS TYPES
        // ADICIONAR CACHE
        // ADICIONAR CACHE
        // ADICIONAR CACHE
        // ADICIONAR CACHE
        $totalUrgentTranscripts = $this->baseQuery($userId, $startToday, $endToday)->where('transcript_type_id', 3)->count();

        return [
            'totalTranscripts' => $totalTranscripts,
            'totalTimeTranscripts' => $totalTimeTranscripts,
            'totalUrgentTranscripts' => $totalUrgentTranscripts,
            'averageTranscriptsTime' => $this->averageTranscriptsTime($totalTimeTranscripts, $totalTranscripts),
        ];
    }

    public function charts()
    {
        $userId = Auth::id();

        return [
            'transcriptsByWeek' => $this->currentWeekTranscripts($userId),
            'transcriptsByType' => $this->countTranscriptByType($userId)
        ];
    }

    public function latestRecentTranscripts()
    {
        return Transcript::select('id', 'patient', 'end_conversation_time', 'transcript_type_id')
            ->with([
                'transcriptType:id,type',
                'document:id,transcript_id,document_template_id',
                'document.documentTemplate:id,name'
            ])
            ->where('user_id', 1)
            ->latest()
            ->limit(4)
            ->get();
    }

    private function baseQuery($userId, $start, $end)
    {
        return Transcript::query()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end]);
    }

    private function averageTranscriptsTime($totalTimeTranscripts, $totalTranscripts)
    {
        if($totalTranscripts <= 0) return 0;

        return $totalTimeTranscripts / $totalTranscripts;
    }

    private function currentWeekTranscripts($userId)
    {
        return Transcript::query()
            ->selectRaw("
                EXTRACT(DOW FROM created_at AT TIME ZONE 'America/Sao_Paulo') as day_of_week,
                COUNT(*) as total
            ")
            ->where('user_id', $userId)
            ->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get();
    }

    private function countTranscriptByType($userId) {
        return TranscriptType::select(['id', 'type'])
            ->withCount([
                'transcripts' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                    $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                }
            ])
            ->get();
    }
}