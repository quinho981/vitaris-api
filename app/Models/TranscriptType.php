<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranscriptType extends Model
{
    use SoftDeletes;

    protected $table = 'transcript_types';

    protected $fillable = [
        'type'
    ];

    public function transcripts(): HasMany
    {
        return $this->hasMany(Transcript::class);
    }
}
