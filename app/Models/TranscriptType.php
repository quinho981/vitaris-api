<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranscriptType extends Model
{
    use SoftDeletes;

    protected $table = 'transcript_types';

    protected $fillable = [
        'type'
    ];

    public function transcript(): HasOne
    {
        return $this->hasOne(Transcript::class);
    }
}
