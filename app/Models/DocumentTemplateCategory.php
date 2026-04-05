<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplateCategory extends Model
{
    use SoftDeletes;

    protected $table = 'document_templates_categories';

    protected $fillable = [
        'name',
        'color',
        'icon'
    ];

        public function templates(): HasMany
        {
            return $this->hasMany(DocumentTemplate::class, 'category_id');
        }
}
