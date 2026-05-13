<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transcript_id',
        'document_template_id',
        'patient',
        'result'
    ];

    public function transcript(): BelongsTo 
    {
        return $this->belongsTo(Transcript::class);
    }

    public function documentTemplate(): BelongsTo 
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function ai_insights(): HasOne 
    {
        return $this->hasOne(AiInsights::class);
    }

    public function scopeFromUserBetweenDatesViaTranscript(Builder $query, int $userId, Carbon $start, Carbon $end): Builder
    {
        return $query
            ->whereHas('transcript', function (Builder $query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereBetween('created_at', [$start, $end]);
    }
}
