<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiInsights extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'red_flags',
        'case_severity',
        'brief_description',
        'possible_diagnoses',
        'suggested_cid_codes',
        'suggested_exams',
        'suggested_conducts',
        'missing_clinical_information'
    ];

    protected $casts = [
        'red_flags' => 'array',
        'case_severity' => 'array',
        'brief_description' => 'array',
        'possible_diagnoses' => 'array',
        'suggested_cid_codes' => 'array',
        'suggested_exams' => 'array',
        'suggested_conducts' => 'array',
        'missing_clinical_information' => 'array'
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
