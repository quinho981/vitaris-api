<?php

namespace App\Observers;

use App\Models\Document;
use Illuminate\Support\Facades\Cache;

class DocumentObserver
{
    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document):void
    {
        $this->clearCache($document);
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        $this->clearCache($document);
    }

    private function clearCache(Document $document): void
    {
        $userId = $document->transcript()->withTrashed()->value('user_id');
        Cache::forget("top_templates_user_{$userId}");
    }
}
