<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transcript_id',
        'document_type_id',
        'patient',
        'result'
    ];

    public function transcript(): BelongsTo 
    {
        return $this->belongsTo(Transcript::class);
    }

    public function documentType(): BelongsTo 
    {
        return $this->belongsTo(DocumentType::class);
    }
}
