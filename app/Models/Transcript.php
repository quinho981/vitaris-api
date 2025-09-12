<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transcript extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'patient',
        'conversation',
        'end_conversation_time',
        'transcript_type_id',
    ];

    protected $casts = [
        'conversation' => 'array',
    ];

    public function user(): BelongsTo 
    {
        return $this->belongsTo(User::class);
    }

    public function document(): HasOne 
    {
        return $this->hasOne(Document::class);
    }

    public function transcriptType(): BelongsTo 
    {
        return $this->belongsTo(TranscriptType::class);
    }
}
