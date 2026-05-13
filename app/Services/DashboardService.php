<?php

namespace App\Services;

use App\Enums\TranscriptsTypeEnum;
use App\Models\Document;
use App\Models\Transcript;
use App\Models\TranscriptType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function summary(): array
    {
        $userId = Auth::id();
        $startToday = now()->startOfDay();
        $endToday = now()->endOfDay();
        
        $transcriptsCountToday = Cache::remember("dashboard:summary:today:{$userId}", 300, function () use ($userId, $startToday, $endToday) {
                                return $this->transcriptsTodayQuery($userId, $startToday, $endToday)->count();
                            });

        $transcriptsDurationToday = Cache::remember("dashboard:summary:time:{$userId}", 300, function () use ($userId, $startToday, $endToday) {
                                return $this->transcriptsTodayQuery($userId, $startToday, $endToday)->sum('end_conversation_time');
                            });

        $urgentTranscriptsCountToday = Cache::remember("dashboard:summary:urgent:{$userId}", 300, function () use ($userId, $startToday, $endToday) {
                                return $this->transcriptsTodayQuery($userId, $startToday, $endToday)->where('transcript_type_id', TranscriptsTypeEnum::URGENTE->value)->count();
                            });

        $transcriptsCountWithTrashedToday = $this->transcriptsTodayQuery($userId, $startToday, $endToday)->withTrashed()->count();
        $documentCountWithTrashehdToday = $this->documentsTodayQuery($userId, $startToday, $endToday)->withTrashed()->count();

        return [
            'transcriptsCountToday' => $transcriptsCountToday,
            'transcriptsDurationToday' => $transcriptsDurationToday,
            'urgentTranscriptsCountToday' => $urgentTranscriptsCountToday,
            'averageTranscriptsTime' => $this->averageTranscriptsTime($transcriptsDurationToday, $transcriptsCountToday),
            
            'transcriptsCountWithTrashedToday' => $transcriptsCountWithTrashedToday,
            'transcriptDiscarded' => $this->transcriptsTodayQuery($userId, $startToday, $endToday)->onlyTrashed()->count(),
            'documentCountWithTrashehdToday' => $documentCountWithTrashehdToday,
            'documentDiscarded' => $this->documentsTodayQuery($userId, $startToday, $endToday)->onlyTrashed()->count()
        ];
    }

    public function charts(): array
    {
        $userId = Auth::id();
        $startWeek = now()->startOfWeek();
        $endWeek = now()->endOfWeek();

        return [
            'transcriptsByWeek' => $this->currentWeekTranscripts($userId, $startWeek, $endWeek),
            'transcriptsByType' => $this->countWeekTranscriptByType($userId, $startWeek, $endWeek)
        ];
    }

    public function latestRecentTranscripts(): Collection
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

    private function transcriptsTodayQuery(int $userId, Carbon $start, Carbon $end)
    {
        return Transcript::fromUserBetWeenDates($userId, $start, $end);
    }

    private function documentsTodayQuery(int $userId, Carbon $start, Carbon $end)
    {
        return Document::fromUserBetweenDatesViaTranscript($userId, $start, $end);
    }

    private function averageTranscriptsTime(int|float $totalTimeTranscripts, int $totalTranscripts)
    {
        if($totalTranscripts <= 0) return 0;
        return $totalTimeTranscripts / $totalTranscripts;
    }

    private function currentWeekTranscripts(int $userId, Carbon $startWeek, Carbon $endWeek)
    {
        return Cache::remember("dashboard:charts:week:{$userId}", 600, function () use ($userId, $startWeek, $endWeek) {
            return Transcript::query()
                ->selectRaw("
                    EXTRACT(DOW FROM created_at AT TIME ZONE 'America/Sao_Paulo') as day_of_week,
                    COUNT(*) as total
                ")
                ->where('user_id', $userId)
                ->whereBetween('created_at', [$startWeek, $endWeek])
                ->groupBy('day_of_week')
                ->orderBy('day_of_week')
                ->get()
                ->toArray();
        });
    }

    private function countWeekTranscriptByType(int $userId, Carbon $startWeek, Carbon $endWeek)
    {
        return Cache::remember("dashboard:charts:type:{$userId}", 600, function () use ($userId, $startWeek, $endWeek) {
            return TranscriptType::select(['id', 'type'])
                ->withCount([
                    'transcripts' => function ($query) use ($userId, $startWeek, $endWeek) {
                        $query->where('user_id', $userId);
                        $query->whereBetween('created_at', [$startWeek, $endWeek]);
                    }
                ])
                ->get()
                ->toArray();
        });
    }

    public static function clear(int $userId)
    {
        Cache::forget("dashboard:summary:today:{$userId}");
        Cache::forget("dashboard:summary:time:{$userId}");
        Cache::forget("dashboard:summary:urgent:{$userId}");
        Cache::forget("dashboard:charts:week:{$userId}");
        Cache::forget("dashboard:charts:type:{$userId}");
    }
}