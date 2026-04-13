<?php

namespace App\Services;

use App\Enums\TranscriptsType;
use App\Models\Transcript;
use App\Models\TranscriptType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function summary() 
    {
        $userId = Auth::id();
        $startToday = now()->startOfDay();
        $endToday = now()->endOfDay();
        
        $data = Cache::remember(
            "summary_today:{$userId}",
            300,
            function () use ($userId, $startToday, $endToday) { 
                return $this->baseQuery($userId, $startToday, $endToday)
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(end_conversation_time) as total_time,
                        SUM(CASE WHEN transcript_type_id = ? THEN 1 ELSE 0 END) as urgent
                    ', [TranscriptsType::URGENTE->value])
                    ->first();
            }
        );
        return [
            'totalTranscripts' => (int) $data->total,
            'totalTimeTranscripts' => (float) ($data->total_time ?? 0 ),
            'totalUrgentTranscripts' => (int) ($data->urgent ?? 0),
            'averageTranscriptsTime' => $this->averageTranscriptsTime($data->total_time, $data->total),
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
        $userId = Auth::id();

        return Transcript::select('id', 'patient', 'end_conversation_time', 'transcript_type_id')
            ->with([
                'transcriptType:id,type',
                'document:id,transcript_id,document_template_id',
                'document.documentTemplate:id,name'
            ])
            ->where('user_id', $userId)
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
        return Cache::remember("dashboard:charts:week:{$userId}", 600, function () use ($userId) {
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
            ->get()
            ->toArray();
        });
    }

    private function countTranscriptByType($userId) {
        return Cache::remember("dashboard:charts:type:{$userId}", 600, function () use ($userId) {
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
                ->get()
                ->toArray();
        });
    }

    public static function clear($userId)
    {
        Cache::forget("dashboard:summary_today:{$userId}");
        Cache::forget("dashboard:charts:week:{$userId}");
        Cache::forget("dashboard:charts:type:{$userId}");
    }
}