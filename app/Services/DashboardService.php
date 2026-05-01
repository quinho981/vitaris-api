<?php

namespace App\Services;

use App\Enums\TranscriptsTypeEnum;
use App\Models\Document;
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
        
        $transcriptsCountToday = Cache::remember("dashboard:summary:today:{$userId}", 300, function () use ($userId, $startToday, $endToday) {
                                return $this->baseQuery($userId, $startToday, $endToday)->count();
                            });

        $transcriptsDurationToday = Cache::remember("dashboard:summary:time:{$userId}", 300, function () use ($userId, $startToday, $endToday) {
                                return $this->baseQuery($userId, $startToday, $endToday)->sum('end_conversation_time');
                            });

        $urgentTranscriptsCountToday = Cache::remember("dashboard:summary:urgent:{$userId}", 300, function () use ($userId, $startToday, $endToday) {
                                return $this->baseQuery($userId, $startToday, $endToday)->where('transcript_type_id', TranscriptsTypeEnum::URGENTE->value)->count();
                            });

        $transcriptsCountWithTrashedToday = $this->baseQuery($userId, $startToday, $endToday)->withTrashed()->count();
        $documentCountWithTrashehdToday = $this->getDocumentsWithTrashed($userId, $startToday, $endToday);

        return [
            'transcriptsCountToday' => $transcriptsCountToday,
            'transcriptsDurationToday' => $transcriptsDurationToday,
            'urgentTranscriptsCountToday' => $urgentTranscriptsCountToday,
            'averageTranscriptsTime' => $this->averageTranscriptsTime($transcriptsDurationToday, $transcriptsCountToday),
            
            'transcriptsCountWithTrashedToday' => $transcriptsCountWithTrashedToday,
            'transcriptDiscarded' => $this->baseQuery($userId, $startToday, $endToday)->onlyTrashed()->count(),
            'documentCountWithTrashehdToday' => $documentCountWithTrashehdToday,
            'documentDiscarded' => $this->documentBaseQuery($userId, $startToday, $endToday)->onlyTrashed()->count()
        ];
    }

    public function charts()
    {
        $userId = Auth::id();

        return [
            'transcriptsByWeek' => $this->currentWeekTranscripts($userId),
            'transcriptsByType' => $this->countWeekTranscriptByType($userId)
        ];
    }

    public function latestRecentTranscripts()
    {
        $userId = Auth::id();

        return Transcript::select('id', 'patient', 'end_conversation_time', 'transcript_type_id', 'created_at')
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

    private function getDocumentsWithTrashed($userId, $start, $end)
    {
        return $this->documentBaseQuery($userId, $start, $end)
            ->withTrashed()
            ->count();
    }

    private function documentBaseQuery($userId, $start, $end)
    {
        return Document::query()
            ->whereHas('transcript', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
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
                // ->withTrashed()
                ->groupBy('day_of_week')
                ->orderBy('day_of_week')
                ->get()
                ->toArray();
        });
    }

    private function countWeekTranscriptByType($userId) {
        return Cache::remember("dashboard:charts:type:{$userId}", 600, function () use ($userId) {
            return TranscriptType::select(['id', 'type'])
                ->withCount([
                    'transcripts' => function ($query) use ($userId) {
                        // $query->withTrashed();
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
        Cache::forget("dashboard:summary:today:{$userId}");
        Cache::forget("dashboard:summary:time:{$userId}");
        Cache::forget("dashboard:summary:urgent:{$userId}");
        Cache::forget("dashboard:charts:week:{$userId}");
        Cache::forget("dashboard:charts:type:{$userId}");
    }
}