<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiInsights extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'main_topics',
        'identified_symptoms',
        'possible_diagnoses',
    ];

    protected $casts = [
        'main_topics' => 'array',
        'identified_symptoms' => 'array',
        'possible_diagnoses' => 'array',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
