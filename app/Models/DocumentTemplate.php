<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'document_templates';

    protected $fillable = [
        'name'
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
